<?php

class rex_stream_test extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testStreamInclude()
    {
        $content = 'MY_TEST';
        $streamUrl = rex_stream::factory('test-stream', $content);
        ob_start();
        require $streamUrl;
        $result = ob_get_clean();

        $this->assertEquals($result, $content);
    }
}
