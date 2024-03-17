<?php

namespace Redaxo\Core\Tests\Util;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Core;
use Redaxo\Core\Util\Timer;
use RuntimeException;
use Throwable;

/** @internal */
final class TimerTest extends TestCase
{
    /** @var array{enabled: bool, throw_always_exception: bool|int} */
    private array $orgDebug;

    protected function setUp(): void
    {
        // Timer internals depend on debug mode..
        $this->orgDebug = Core::getProperty('debug');
        Core::setProperty('debug', true);
    }

    protected function tearDown(): void
    {
        Core::setProperty('debug', $this->orgDebug);
    }

    public function testMeasure(): void
    {
        $callable = static function () {
            /** @var int $i */
            static $i = 1;
            usleep(1);
            return 'result' . ($i++);
        };

        $result = Timer::measure('test', $callable);
        self::assertSame('result1', $result);

        self::assertArrayHasKey('test', Timer::$serverTimings);
        $timing = Timer::$serverTimings['test'];
        self::assertIsFloat($timing['sum']);
        self::assertGreaterThan(0, $timing['sum']);
        self::assertArrayHasKey(0, $timing['timings']);
        self::assertIsFloat($timing['timings'][0]['start']);
        self::assertIsFloat($timing['timings'][0]['end']);
        self::assertGreaterThan($timing['timings'][0]['start'], $timing['timings'][0]['end']);

        $result = Timer::measure('test', $callable);

        self::assertSame('result2', $result);
        self::assertGreaterThan($timing['sum'], Timer::$serverTimings['test']['sum']);

        $exception = null;
        try {
            Timer::measure('test2', static function () {
                throw new RuntimeException();
            });
        } catch (Throwable $exception) {
        }

        self::assertInstanceOf(RuntimeException::class, $exception);

        self::assertArrayHasKey('test2', Timer::$serverTimings);
        $timing = Timer::$serverTimings['test2'];
        self::assertIsFloat($timing['sum']);
        self::assertGreaterThan(0, $timing['sum']);
        self::assertArrayHasKey(0, $timing['timings']);
        self::assertIsFloat($timing['timings'][0]['start']);
        self::assertIsFloat($timing['timings'][0]['end']);
        self::assertGreaterThan($timing['timings'][0]['start'], $timing['timings'][0]['end']);
    }
}
