<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_socket_test extends TestCase
{
    private ?string $proxy = null;

    protected function setUp(): void
    {
        $this->proxy = rex::getProperty('socket_proxy');
        rex::setProperty('socket_proxy', null);
    }

    protected function tearDown(): void
    {
        rex::setProperty('socket_proxy', $this->proxy);
    }

    public function testFactory(): rex_socket
    {
        $socket = rex_socket::factory('www.example.com');
        $socket->setOptions([]);
        static::assertEquals(rex_socket::class, $socket::class);
        return $socket;
    }

    public function testFactoryProxy(): void
    {
        rex::setProperty('socket_proxy', 'proxy.example.com:8888');
        $socket = rex_socket::factory('www.example.com');
        $socket->setOptions([]);
        static::assertEquals(rex_socket_proxy::class, $socket::class);
    }

    public function testFactoryUrl(): void
    {
        $socket = rex_socket::factoryUrl('www.example.com');
        $socket->setOptions([]);
        static::assertEquals(rex_socket::class, $socket::class);
    }

    public function testFactoryUrlProxy(): void
    {
        rex::setProperty('socket_proxy', 'proxy.example.com:8888');
        $socket = rex_socket::factoryUrl('www.example.com');
        $socket->setOptions([]);
        static::assertEquals(rex_socket_proxy::class, $socket::class);
    }

    #[Depends('testFactory')]
    public function testWriteRequest(rex_socket $socket): void
    {
        $class = new ReflectionClass(rex_socket::class);
        $property = $class->getProperty('stream');
        $method = $class->getMethod('writeRequest');

        $stream = fopen('php://temp', 'r+');
        $property->setValue($socket, $stream);
        $response = $method->invoke($socket, 'GET', '/a/path', ['Host' => 'www.example.com', 'Connection' => 'Close'], "body1\r\nbody2");

        static::assertInstanceOf(rex_socket_response::class, $response);

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

    /** @return list<array{string, string, int, bool, string}> */
    public static function parseUrlProvider(): array
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

    #[DataProvider('parseUrlProvider')]
    public function testParseUrl(string $url, string $expectedHost, int $expectedPort, bool $expectedSsl, string $expectedPath): void
    {
        $method = new ReflectionMethod(rex_socket::class, 'parseUrl');
        $result = $method->invoke(null, $url);
        $expected = [
            'host' => $expectedHost,
            'port' => $expectedPort,
            'ssl' => $expectedSsl,
            'path' => $expectedPath,
        ];
        static::assertEquals($expected, $result);
    }

    /** @return list<array{string}> */
    public static function parseUrlExceptionProvider(): array
    {
        return [
            [''],
            ['http://'],
            ['abc://example.com'],
        ];
    }

    #[DataProvider('parseUrlExceptionProvider')]
    public function testParseUrlException(string $url): void
    {
        $this->expectException(rex_socket_exception::class);

        $method = new ReflectionMethod(rex_socket::class, 'parseUrl');
        $method->invoke(null, $url);
    }
}
