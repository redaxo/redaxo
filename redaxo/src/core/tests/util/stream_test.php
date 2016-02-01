<?php

class rex_stream_test extends PHPUnit_Framework_TestCase
{
    public function testStreamInclude()
    {
        $content = 'foo <?php echo "bar";';
        $streamUrl = rex_stream::factory('test-stream/1', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        $this->assertEquals('foo bar', $result);
    }

    public function testStreamIncludeWithRealFile()
    {
        $property = new ReflectionProperty('rex_stream', 'useRealFiles');
        $property->setAccessible(true);
        $property->setValue(true);

        $content = 'foo <?php echo "bar";';
        $streamUrl = rex_stream::factory('test-stream/2', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        $this->assertEquals('foo bar', $result);

        $property->setValue(null);
    }
}
