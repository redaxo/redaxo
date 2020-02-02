<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_socket_response_test extends TestCase
{
    private function getResponse($content)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        fseek($stream, 0);

        return new rex_socket_response($stream);
    }

    public function getStatusProvider()
    {
        return [
            ['',                              null, null,                'isInvalid'],
            ['abc',                           null, null,                'isInvalid'],
            ['200 OK',                        null, null,                'isInvalid'],
            ['HTTP/1.1 99 Message',             99, 'Message',           'isInvalid'],
            ['HTTP/1.1 600 Message',           600, 'Message',           'isInvalid'],
            ['HTTP/1.1 100 Continue',          100, 'Continue',          'isInformational'],
            ['HTTP/1.1 200 OK',                200, 'OK',                'isSuccessful'],
            ['HTTP/1.1 301 Moved Permanently', 301, 'Moved Permanently', 'isRedirection'],
            ['HTTP/1.1 404 Not Found',         404, 'Not Found',         'isClientError'],
            ['HTTP/1.1 501 Not Implemented',   501, 'Not Implemented',   'isServerError'],
        ];
    }

    /**
     * @dataProvider getStatusProvider
     */
    public function testGetStatus($header, $statusCode, $statusMessage, $positiveMethod)
    {
        $response = $this->getResponse($header . "\r\n");

        static::assertSame($statusCode, $response->getStatusCode(), 'getStatusCode()');
        static::assertSame($statusMessage, $response->getStatusMessage(), 'getStatusMessage()');
        static::assertSame(200 == $statusCode, $response->isOk(), 'isOk()');

        $methods = ['isInformational', 'isSuccessful', 'isRedirection', 'isClientError', 'isServerError', 'isInvalid'];
        foreach ($methods as $method) {
            static::assertSame($positiveMethod == $method, $response->$method(), $method . '()');
        }
    }

    public function testGetHeader()
    {
        $header = "HTTP/1.1 200 OK\r\nKey1: Value1\r\nkey2: Value2";
        $response = $this->getResponse($header . "\r\n\r\nbody\r\nbody");

        static::assertSame($header, $response->getHeader(), 'getHeader() without params returns full header');
        static::assertSame('Value1', $response->getHeader('Key1'), 'getHeader($key) returns the value of the key');
        static::assertSame('Value2', $response->getHeader('Key2', 'default'), 'getHeader($key, $default) returns the value of the key');
        static::assertNull($response->getHeader('Key3'), 'getHeader($key) returns null for non-existing keys');
        static::assertSame('default', $response->getHeader('Key3', 'default'), 'getHeader($key, $default) returns $default for non-existing keys');
    }

    public function testGetBody()
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        static::assertSame($body, $response->getBody());
    }

    public function testWriteBodyTo()
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        $temp = fopen('php://temp', 'r+');
        $response->writeBodyTo($temp);
        fseek($temp, 0);
        static::assertSame($body, fread($temp, 1024));
        fclose($temp);
    }
}
