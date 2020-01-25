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
        $this->assertSame('result1', $result);

        $this->assertArrayHasKey('test', rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test'];
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

        $this->assertInstanceOf(RuntimeException::class, $exception);

        $this->assertArrayHasKey('test2', rex_timer::$serverTimings);
        $timing = rex_timer::$serverTimings['test2'];
        $this->assertIsFloat($timing);
        $this->assertGreaterThan(0, $timing);
    }
}
