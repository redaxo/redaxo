<?php

class rex_socket_response_test extends PHPUnit_Framework_TestCase
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
        return array(
            array('',                              null, null,                'isInvalid'),
            array('abc',                           null, null,                'isInvalid'),
            array('200 OK',                        null, null,                'isInvalid'),
            array('HTTP/1.1 99 Message',             99, 'Message',           'isInvalid'),
            array('HTTP/1.1 600 Message',           600, 'Message',           'isInvalid'),
            array('HTTP/1.1 100 Continue',          100, 'Continue',          'isInformational'),
            array('HTTP/1.1 200 OK',                200, 'OK',                'isSuccessful'),
            array('HTTP/1.1 301 Moved Permanently', 301, 'Moved Permanently', 'isRedirection'),
            array('HTTP/1.1 404 Not Found',         404, 'Not Found',         'isClientError'),
            array('HTTP/1.1 501 Not Implemented',   501, 'Not Implemented',   'isServerError')
        );
    }

    /**
     * @dataProvider getStatusProvider
     */
    public function testGetStatus($header, $statusCode, $statusMessage, $positiveMethod)
    {
        $response = $this->getResponse($header . "\r\n");

        $this->assertSame($statusCode, $response->getStatusCode(), 'getStatusCode()');
        $this->assertSame($statusMessage, $response->getStatusMessage(), 'getStatusMessage()');
        $this->assertSame($statusCode == 200, $response->isOk(), 'isOk()');

        $methods = array('isInformational', 'isSuccessful', 'isRedirection', 'isClientError', 'isServerError', 'isInvalid');
        foreach ($methods as $method) {
            $this->assertSame($positiveMethod == $method, $response->$method(), $method . '()');
        }
    }

    public function testGetHeader()
    {
        $header = "HTTP/1.1 200 OK\r\nKey1: Value1\r\nkey2: Value2";
        $response = $this->getResponse($header . "\r\n\r\nbody\r\nbody");

        $this->assertSame($header,   $response->getHeader(),                  'getHeader() without params returns full header');
        $this->assertSame('Value1',  $response->getHeader('Key1'),            'getHeader($key) returns the value of the key');
        $this->assertSame('Value2',  $response->getHeader('Key2', 'default'), 'getHeader($key, $default) returns the value of the key');
        $this->assertSame(null,      $response->getHeader('Key3'),            'getHeader($key) returns null for non-existing keys');
        $this->assertSame('default', $response->getHeader('Key3', 'default'), 'getHeader($key, $default) returns $default for non-existing keys');
    }

    public function testGetBody()
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        $this->assertSame($body, $response->getBody());
    }

    public function testWriteBodyTo()
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        $temp = fopen('php://temp', 'r+');
        $response->writeBodyTo($temp);
        fseek($temp, 0);
        $this->assertSame($body, fread($temp, 1024));
        fclose($temp);
    }
}
