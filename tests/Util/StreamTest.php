<?php

namespace Redaxo\Core\Tests\Util;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Util\Stream;
use ReflectionClass;

/**
 * @internal
 */
class StreamTest extends TestCase
{
    public function testStreamInclude(): void
    {
        $content = 'foo <?php echo "bar";';
        $streamUrl = Stream::factory('test-stream/1', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        self::assertEquals('foo bar', $result);
    }

    public function testStreamIncludeWithRealFile(): void
    {
        $class = new ReflectionClass(Stream::class);
        $class->setStaticPropertyValue('useRealFiles', true);

        $content = 'foo <?php echo "bar";';
        $streamUrl = Stream::factory('test-stream/2', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        self::assertEquals('foo bar', $result);

        $class->setStaticPropertyValue('useRealFiles', null);
    }
}
