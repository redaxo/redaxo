<?php

namespace Redaxo\Core\Tests\Cronjob;

use DateTimeImmutable;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Cronjob\Cronjob;
use Redaxo\Core\Cronjob\CronjobManager;
use Redaxo\Core\Cronjob\CronjobRegistry;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\InvalidArgumentException;
use Redaxo\Core\Tests\Cronjob\Fixtures\CronjobFixture;
use Redaxo\Test\TestAddon;
use ReflectionProperty;

/** @internal */
final class CronjobManagerTest extends TestCase
{
    /** @var array<mixed> */
    private array $origServer = [];

    /** @var array<mixed> */
    private array $origEnv = [];

    #[Override]
    protected function setUp(): void
    {
        $this->origServer = $_SERVER;
        $this->origEnv = $_ENV;
        unset($_SERVER['REX_CRONJOBS'], $_ENV['REX_CRONJOBS']);

        CronjobFixture::$calls = 0;
        $this->deleteStates();
    }

    #[Override]
    protected function tearDown(): void
    {
        $_SERVER = $this->origServer;
        $_ENV = $this->origEnv;

        CronjobRegistry::reset();
        $this->deleteStates();
    }

    /** @param array{all: bool, names: list<string>} $expected */
    #[DataProvider('provideGetSetting')]
    public function testGetSetting(array $expected, ?string $value): void
    {
        if (null !== $value) {
            $_SERVER['REX_CRONJOBS'] = $value;
        }

        self::assertSame($expected, CronjobManager::getSetting());
    }

    /** @return iterable<int, array{array{all: bool, names: list<string>}, ?string}> */
    public static function provideGetSetting(): iterable
    {
        yield [['all' => true, 'names' => []], null];
        yield [['all' => true, 'names' => []], ''];
        yield [['all' => true, 'names' => []], 'all'];
        yield [['all' => true, 'names' => ['foo', 'bar']], 'all, -foo,-bar'];
        yield [['all' => false, 'names' => []], 'none'];
        yield [['all' => false, 'names' => ['foo']], 'foo'];
        yield [['all' => false, 'names' => ['foo', 'bar-baz']], ' foo, bar-baz ,,'];
    }

    #[DataProvider('provideGetSettingInvalid')]
    public function testGetSettingInvalid(string $value): void
    {
        $_SERVER['REX_CRONJOBS'] = $value;

        $this->expectException(InvalidArgumentException::class);

        CronjobManager::getSetting();
    }

    /** @return iterable<int, array{string}> */
    public static function provideGetSettingInvalid(): iterable
    {
        yield ['-foo'];
        yield ['foo,-bar'];
        yield ['all,foo'];
        yield ['all,-'];
        yield ['all,-none'];
        yield ['none,foo'];
        yield ['foo,all'];
    }

    public function testIsEnabled(): void
    {
        self::assertTrue(CronjobManager::isEnabled('phpunit_a'));

        $_SERVER['REX_CRONJOBS'] = 'phpunit_a,phpunit_unknown';
        self::assertTrue(CronjobManager::isEnabled('phpunit_a'));
        self::assertFalse(CronjobManager::isEnabled('phpunit_b'));

        $this->setRegistry($this->cronjob('phpunit_a', 'succeed'));
        self::assertSame(['phpunit_unknown'], CronjobManager::getUnknownNames());

        $_SERVER['REX_CRONJOBS'] = 'all,-phpunit_a,-phpunit_unknown';
        self::assertFalse(CronjobManager::isEnabled('phpunit_a'));
        self::assertTrue(CronjobManager::isEnabled('phpunit_b'));
        self::assertSame(['phpunit_unknown'], CronjobManager::getUnknownNames());

        $_SERVER['REX_CRONJOBS'] = 'none';
        self::assertFalse(CronjobManager::isEnabled('phpunit_a'));
        self::assertSame([], CronjobManager::getUnknownNames());
    }

    public function testRun(): void
    {
        $result = CronjobManager::run($this->cronjob('phpunit_a', 'succeed'));

        self::assertSame('phpunit_a', $result->name);
        self::assertTrue($result->success);
        self::assertSame('done', $result->message);
        self::assertSame(1, CronjobFixture::$calls);

        $state = CronjobManager::getStates()['phpunit_a'];
        self::assertTrue($state['lastSuccess']);
        self::assertFalse($state['running']);
        self::assertNotNull($state['lastRun']);

        $result = CronjobManager::run($this->cronjob('phpunit_b', 'succeedVoid'));
        self::assertTrue($result->success);
        self::assertSame('', $result->message);
        self::assertSame(2, CronjobFixture::$calls);
    }

    public function testRunOnAddonInstance(): void
    {
        $result = CronjobManager::run(new Cronjob('phpunit_addon_instance', '@daily', TestAddon::class, 'cronjob', false));

        self::assertTrue($result->success);
        self::assertSame('Called on addon test', $result->message);
    }

    #[DataProvider('provideRunFailing')]
    public function testRunFailing(string $expectedMessage, string $method): void
    {
        // failures are written to the system log, which forwards them to the php error log as well
        $errorLog = ini_set('error_log', '/dev/null');
        try {
            $result = CronjobManager::run($this->cronjob('phpunit_a', $method));
        } finally {
            ini_set('error_log', (string) $errorLog);
        }

        self::assertFalse($result->success);
        self::assertStringContainsString($expectedMessage, $result->message);

        $state = CronjobManager::getStates()['phpunit_a'];
        self::assertFalse($state['lastSuccess']);
        self::assertFalse($state['running']);
    }

    /** @return iterable<int, array{string, string}> */
    public static function provideRunFailing(): iterable
    {
        yield ['failed on purpose', 'fail'];
        yield ['must return a string or null, int returned', 'returnInvalid'];
    }

    public function testRunWhileRunning(): void
    {
        CronjobManager::run($this->cronjob('phpunit_a', 'succeed'));
        Sql::factory()->setQuery('UPDATE rex_cronjob SET execution_start = NOW() WHERE name = ?', ['phpunit_a']);

        $result = CronjobManager::run($this->cronjob('phpunit_a', 'succeed'));

        self::assertFalse($result->success);
        self::assertSame(1, CronjobFixture::$calls);
    }

    public function testRunDue(): void
    {
        $this->setRegistry(
            $due = $this->cronjob('phpunit_due', 'succeed'),
            $notDue = $this->cronjob('phpunit_not_due', 'succeed'),
            $disabled = $this->cronjob('phpunit_disabled', 'succeed'),
            $stale = $this->cronjob('phpunit_stale', 'succeed'),
            $locked = $this->cronjob('phpunit_locked', 'succeed'),
            $this->cronjob('phpunit_new', 'succeed'),
        );
        $_SERVER['REX_CRONJOBS'] = 'all,-phpunit_disabled';

        $twoDaysAgo = Sql::datetime(new DateTimeImmutable('-2 days')->getTimestamp());
        $this->insertState($due->name, $twoDaysAgo);
        $this->insertState($notDue->name, Sql::datetime());
        $this->insertState($disabled->name, $twoDaysAgo);
        $this->insertState($stale->name, $twoDaysAgo, executionStart: Sql::datetime(new DateTimeImmutable('-7 hours')->getTimestamp()));
        $this->insertState($locked->name, $twoDaysAgo, executionStart: Sql::datetime());

        $results = CronjobManager::runDue();

        self::assertSame(['phpunit_due', 'phpunit_stale'], array_map(static fn ($result) => $result->name, $results));
        self::assertSame(2, CronjobFixture::$calls);

        $states = CronjobManager::getStates();
        // a new cronjob only gets its state, it waits for the next scheduled time
        self::assertArrayHasKey('phpunit_new', $states);
        self::assertNull($states['phpunit_new']['lastSuccess']);

        self::assertSame([], CronjobManager::runDue());
    }

    public function testRemoveOrphanedStates(): void
    {
        $this->insertState('phpunit_kept', Sql::datetime());
        $this->insertState('phpunit_orphaned', Sql::datetime());

        // the states of the instance's real cronjobs must survive the test
        /** @var list<non-empty-string> $names */
        $names = array_diff(array_keys(CronjobManager::getStates()), ['phpunit_orphaned']);
        $this->setRegistry(...array_map(fn (string $name) => $this->cronjob($name, 'succeed'), $names));

        CronjobManager::removeOrphanedStates();

        $states = CronjobManager::getStates();
        self::assertArrayHasKey('phpunit_kept', $states);
        self::assertArrayNotHasKey('phpunit_orphaned', $states);
    }

    /** @param non-empty-string $name */
    private function cronjob(string $name, string $method): Cronjob
    {
        return new Cronjob($name, '@daily', CronjobFixture::class, $method, true);
    }

    private function setRegistry(Cronjob ...$cronjobs): void
    {
        $registry = [];
        foreach ($cronjobs as $cronjob) {
            $registry[$cronjob->name] = $cronjob;
        }

        new ReflectionProperty(CronjobRegistry::class, 'cronjobs')->setValue(null, $registry);
    }

    private function insertState(string $name, string $lastRun, ?string $executionStart = null): void
    {
        $sql = Sql::factory();
        $sql->setTable('rex_cronjob');
        $sql->setValues(['name' => $name, 'last_run' => $lastRun, 'execution_start' => $executionStart]);
        $sql->insert();
    }

    private function deleteStates(): void
    {
        Sql::factory()->setQuery('DELETE FROM rex_cronjob WHERE name LIKE "phpunit\_%"');
    }
}
