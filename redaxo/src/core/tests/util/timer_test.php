<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_timer_test extends TestCase
{
    private $orgDebug;

    protected function setUp()
    {
        // rex_timer internals depend on debug mode..
        $this->orgDebug = rex::getProperty('debug');
        rex::setProperty('debug', true);
    }

    protected function tearDown()
    {
        rex::setProperty('debug', $this->orgDebug);
    }

    public function testMeasure(): void
    {
        $callable = static function () {
            static $i = 1;
            usleep(1);
            return 'result'.($i++);
        };

        $result = rex_timer::measure('test', $callable);
        static::assertSame('result1', $result);

        static::assertArrayHasKey('test', rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test'];
        static::assertIsFloat($timing);
        static::assertGreaterThan(0, $timing);

        $result = rex_timer::measure('test', $callable);

        static::assertSame('result2', $result);
        static::assertGreaterThan($timing, rex_timer::$serverTimings['test']);

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
        static::assertIsFloat($timing);
        static::assertGreaterThan(0, $timing);
    }
}
