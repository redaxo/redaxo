<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_timer_test extends TestCase
{
    /** @var array{enabled: bool, throw_always_exception: bool|int} */
    private array $orgDebug;

    protected function setUp(): void
    {
        // rex_timer internals depend on debug mode..
        $this->orgDebug = rex::getProperty('debug');
        rex::setProperty('debug', true);
    }

    protected function tearDown(): void
    {
        rex::setProperty('debug', $this->orgDebug);
    }

    public function testMeasure(): void
    {
        $callable = static function () {
            /** @var int $i */
            static $i = 1;
            usleep(1);
            return 'result'.($i++);
        };

        $result = rex_timer::measure('test', $callable);
        static::assertSame('result1', $result);

        static::assertArrayHasKey('test', rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test'];
        static::assertIsFloat($timing['sum']);
        static::assertGreaterThan(0, $timing['sum']);
        static::assertArrayHasKey(0, $timing['timings']);
        static::assertIsFloat($timing['timings'][0]['start']);
        static::assertIsFloat($timing['timings'][0]['end']);
        static::assertGreaterThan($timing['timings'][0]['start'], $timing['timings'][0]['end']);

        $result = rex_timer::measure('test', $callable);

        static::assertSame('result2', $result);
        static::assertGreaterThan($timing['sum'], rex_timer::$serverTimings['test']['sum']);

        $exception = null;
        try {
            rex_timer::measure('test2', static function () {
                throw new RuntimeException();
            });
        } catch (Throwable $exception) {
        }

        static::assertInstanceOf(RuntimeException::class, $exception);

        static::assertArrayHasKey('test2', rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test2'];
        static::assertIsFloat($timing['sum']);
        static::assertGreaterThan(0, $timing['sum']);
        static::assertArrayHasKey(0, $timing['timings']);
        static::assertIsFloat($timing['timings'][0]['start']);
        static::assertIsFloat($timing['timings'][0]['end']);
        static::assertGreaterThan($timing['timings'][0]['start'], $timing['timings'][0]['end']);
    }
}
