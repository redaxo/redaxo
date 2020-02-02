<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_socket_test extends TestCase
{
    private $proxy;

    protected function setUp()
    {
        $this->proxy = rex::getProperty('socket_proxy');
        rex::setProperty('socket_proxy', null);
    }

    protected function tearDown()
    {
        rex::setProperty('socket_proxy', $this->proxy);
    }

    public function testFactory()
    {
        $socket = rex_socket::factory('www.example.com');
        static::assertEquals('rex_socket', get_class($socket));

        return $socket;
    }

    public function testFactoryProxy()
    {
        rex::setProperty('socket_proxy', 'proxy.example.com:8888');
        $socket = rex_socket::factory('www.example.com');
        static::assertEquals('rex_socket_proxy', get_class($socket));
    }

    public function testFactoryUrl()
    {
        $socket = rex_socket::factoryUrl('www.example.com');
        static::assertEquals('rex_socket', get_class($socket));
    }

    public function testFactoryUrlProxy()
    {
        rex::setProperty('socket_proxy', 'proxy.example.com:8888');
        $socket = rex_socket::factoryUrl('www.example.com');
        static::assertEquals('rex_socket_proxy', get_class($socket));
    }

    /**
     * @depends testFactory
     */
    public function testWriteRequest($socket)
    {
        $class = new ReflectionClass('rex_socket');
        $property = $class->getProperty('stream');
        $property->setAccessible(true);
        $method = $class->getMethod('writeRequest');
        $method->setAccessible(true);

        $stream = fopen('php://temp', 'r+');
        $property->setValue($socket, $stream);
        $response = $method->invoke($socket, 'GET', '/a/path', ['Host' => 'www.example.com', 'Connection' => 'Close'], "body1\r\nbody2");

        static::assertInstanceOf('rex_socket_response', $response);

        $eol = "\r\n";
        $expected = 'GET /a/path HTTP/1.1' . $eol
                            . 'Host: www.example.com' . $eol
                            . 'Connection: Close' . $eol
                            . 'Content-Length: 12' . $eol
                            . $eol
                            . 'body1' . $eol
                            . 'body2';
        fseek($stream, 0);
        static::assertEquals($expected, fread($stream, 1024));
        fclose($stream);
    }

    public function parseUrlProvider()
    {
        return [
            ['example.com',                             'example.com', 80,  false, '/'],
            ['example.com:81',                          'example.com', 81,  false, '/'],
            ['example.com/a/path/?key=value',           'example.com', 80,  false, '/a/path/?key=value'],
            ['example.com:81/a/path/?key=value',        'example.com', 81,  false, '/a/path/?key=value'],
            ['http://example.com',                      'example.com', 80,  false, '/'],
            ['https://example.com',                     'example.com', 443, true,  '/'],
            ['http://example.com:81',                   'example.com', 81,  false, '/'],
            ['https://example.com:444',                 'example.com', 444, true,  '/'],
            ['http://example.com/a/path/?key=value',    'example.com', 80,  false, '/a/path/?key=value'],
            ['http://example.com:81/a/path/?key=value', 'example.com', 81,  false, '/a/path/?key=value'],
        ];
    }

    /**
     * @dataProvider parseUrlProvider
     */
    public function testParseUrl($url, $expectedHost, $expectedPort, $expectedSsl, $expectedPath)
    {
        $method = new ReflectionMethod('rex_socket', 'parseUrl');
        $method->setAccessible(true);
        $result = $method->invoke(null, $url);
        $expected = [
            'host' => $expectedHost,
            'port' => $expectedPort,
            'ssl' => $expectedSsl,
            'path' => $expectedPath,
        ];
        static::assertEquals($expected, $result);
    }

    public function parseUrlExceptionProvider()
    {
        return [
            [''],
            ['http://'],
            ['abc://example.com'],
        ];
    }

    /**
     * @dataProvider parseUrlExceptionProvider
     */
    public function testParseUrlException($url)
    {
        $this->expectException(\rex_socket_exception::class);

        $method = new ReflectionMethod('rex_socket', 'parseUrl');
        $method->setAccessible(true);
        $method->invoke(null, $url);
    }
}
