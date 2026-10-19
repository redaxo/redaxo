<?php

namespace Redaxo\Core\Tests\Cronjob;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\Cronjob\AsCronjob;
use Redaxo\Core\Cronjob\CronjobRegistry;
use Redaxo\Core\Tests\Cronjob\Fixtures\CronjobFixture;
use Redaxo\Test\TestAddon;

/** @internal */
final class CronjobRegistryTest extends TestCase
{
    public function testBuild(): void
    {
        $cronjobs = CronjobRegistry::build([
            ['class' => CronjobFixture::class, 'method' => 'succeed', 'isStatic' => true, 'attribute' => new AsCronjob('b', '*/5 * * * *')],
            ['class' => TestAddon::class, 'method' => 'cronjob', 'isStatic' => false, 'attribute' => new AsCronjob('a', '@daily')],
        ]);

        self::assertSame(['a', 'b'], array_keys($cronjobs));

        self::assertSame('a', $cronjobs['a']->name);
        self::assertSame('@daily', $cronjobs['a']->schedule);
        self::assertSame(TestAddon::class, $cronjobs['a']->class);
        self::assertSame('cronjob', $cronjobs['a']->method);
        self::assertFalse($cronjobs['a']->isStatic);
    }

    /** @param list<array{class: class-string, method: string, isStatic: bool, attribute: AsCronjob}> $entries */
    #[DataProvider('provideBuildInvalid')]
    public function testBuildInvalid(string $expectedMessage, array $entries): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($expectedMessage);

        CronjobRegistry::build($entries);
    }

    /** @return iterable<string, array{string, list<array{class: class-string, method: string, isStatic: bool, attribute: AsCronjob}>}> */
    public static function provideBuildInvalid(): iterable
    {
        yield 'duplicate name' => ['The cronjob name "a" is used by both', [
            ['class' => CronjobFixture::class, 'method' => 'succeed', 'isStatic' => true, 'attribute' => new AsCronjob('a', '@daily')],
            ['class' => CronjobFixture::class, 'method' => 'succeedVoid', 'isStatic' => true, 'attribute' => new AsCronjob('a', '@hourly')],
        ]];

        yield 'invalid schedule' => ['The cronjob "a" (' . CronjobFixture::class . '::succeed()) has an invalid schedule', [
            ['class' => CronjobFixture::class, 'method' => 'succeed', 'isStatic' => true, 'attribute' => new AsCronjob('a', '61 * * * *')],
        ]];

        yield 'required parameter' => ['must be public and must not require parameters', [
            ['class' => CronjobFixture::class, 'method' => 'requireParam', 'isStatic' => true, 'attribute' => new AsCronjob('a', '@daily')],
        ]];

        foreach (['all', 'none', '-foo', 'foo,bar', 'foo bar'] as $name) {
            yield 'invalid name "' . $name . '"' => ['The cronjob name "' . $name . '"', [
                ['class' => CronjobFixture::class, 'method' => 'succeed', 'isStatic' => true, 'attribute' => new AsCronjob($name, '@daily')],
            ]];
        }

        yield 'non-static outside project and addons' => ['must be static', [
            ['class' => CronjobFixture::class, 'method' => 'nonStatic', 'isStatic' => false, 'attribute' => new AsCronjob('a', '@daily')],
        ]];
    }
}
