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

        self::assertEquals('foo bar', $result);
    }

    public function testStreamIncludeWithRealFile(): void
    {
        $class = new ReflectionClass(rex_stream::class);
        $class->setStaticPropertyValue('useRealFiles', true);

        $content = 'foo <?php echo "bar";';
        $streamUrl = rex_stream::factory('test-stream/2', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        self::assertEquals('foo bar', $result);

        $class->setStaticPropertyValue('useRealFiles', null);
    }
}
