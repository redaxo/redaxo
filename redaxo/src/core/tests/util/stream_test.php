<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_stream_test extends TestCase
{
    public function testStreamInclude(): void
    {
        $content = 'foo <?php echo "bar";';
        $streamUrl = rex_stream::factory('test-stream/1', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        static::assertEquals('foo bar', $result);
    }

    public function testStreamIncludeWithRealFile(): void
    {
        $property = new ReflectionProperty(rex_stream::class, 'useRealFiles');
        $property->setValue(true);

        $content = 'foo <?php echo "bar";';
        $streamUrl = rex_stream::factory('test-stream/2', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        static::assertEquals('foo bar', $result);

        $property->setValue(null);
    }
}
