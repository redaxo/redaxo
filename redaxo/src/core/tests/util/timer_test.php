<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_timer_test extends TestCase
{
    public function testMeasure(): void
    {
        $callable = static function () {
            static $i = 1;
            return 'result'.($i++);
        };

        $result = rex_timer::measure('test', $callable);
        $this->assertArrayHasKey("test", rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test'];

        $this->assertSame('result1', $result);
        $this->assertIsFloat($timing);
        $this->assertGreaterThan(0, $timing);

        $result = rex_timer::measure('test', $callable);

        $this->assertSame('result2', $result);
        $this->assertGreaterThan($timing, rex_timer::$serverTimings['test']);

        $exception = null;
        try {
            rex_timer::measure('test2', static function () {
                throw new RuntimeException();
            });
        } catch (Throwable $exception) {
        }

        $this->assertArrayHasKey("test", rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test'];

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertIsFloat($timing);
        $this->assertGreaterThan(0, $timing);
    }
}
