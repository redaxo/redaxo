<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class rex_socket_response_test extends TestCase
{
    private function getResponse(string $content): rex_socket_response
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        fseek($stream, 0);

        return new rex_socket_response($stream);
    }

    /** @return list<array{string, ?int, ?string, string}> */
    public static function getStatusProvider(): array
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

    #[DataProvider('getStatusProvider')]
    public function testGetStatus(string $header, ?int $statusCode, ?string $statusMessage, string $positiveMethod): void
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

    public function testGetHeader(): void
    {
        $header = "HTTP/1.1 200 OK\r\nKey1: Value1\r\nkey2: Value2";
        $response = $this->getResponse($header . "\r\n\r\nbody\r\nbody");

        static::assertSame($header, $response->getHeader(), 'getHeader() without params returns full header');
        static::assertSame('Value1', $response->getHeader('Key1'), 'getHeader($key) returns the value of the key');
        static::assertSame('Value2', $response->getHeader('Key2', 'default'), 'getHeader($key, $default) returns the value of the key');
        static::assertNull($response->getHeader('Key3'), 'getHeader($key) returns null for non-existing keys');
        static::assertSame('default', $response->getHeader('Key3', 'default'), 'getHeader($key, $default) returns $default for non-existing keys');
    }

    public function testGetBody(): void
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        static::assertSame($body, $response->getBody());
    }

    public function testWriteBodyTo(): void
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        $temp = fopen('php://temp', 'r+');
        $response->writeBodyTo($temp);
        fseek($temp, 0);
        static::assertSame($body, fread($temp, 1024));
        fclose($temp);
    }

    public function testGetBodyWithEncoding(): void
    {
        $body = "This is the\r\noriginal content";

        static::assertSame($body, $this->createResponseWithEncoding('gzip',
            zlib_encode($body, ZLIB_ENCODING_GZIP), )->getBody());

        static::assertSame($body, $this->createResponseWithEncoding('deflate',
            zlib_encode($body, ZLIB_ENCODING_DEFLATE), )->getBody());

        // Test combination with chunked with real responses from the redaxo webservice
        $decodedResponseContent =
            file_get_contents(__DIR__.'/socket_response_testfiles/response_decoded');

        static::assertSame($decodedResponseContent, $this->getResponse(
            file_get_contents(__DIR__.'/socket_response_testfiles/response_chunked.testresp'),
        )->decompressContent(true)->getBody());

        static::assertSame($decodedResponseContent, $this->getResponse(
            file_get_contents(__DIR__.'/socket_response_testfiles/response_chunked_gzip.testresp'),
        )->decompressContent(true)->getBody());
    }

    public function testEncodingHeader(): void
    {
        static::assertIsArray($this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\nTest")
            ->getContentEncodings(), );

        static::assertCount(0, $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\nTest")
            ->getContentEncodings(), );

        static::assertIsArray($this->createResponseWithEncoding('gzip, deflate', 'test')
            ->getContentEncodings(), );

        static::assertSame(['gzip', 'deflate'], $this->createResponseWithEncoding('gzip, deflate', 'test')
            ->getContentEncodings(), );

        static::assertSame(['gzip'], $this->createResponseWithEncoding('gzip', 'test')
            ->getContentEncodings(), );
    }

    private function createResponseWithEncoding(string $encoding, string $body): rex_socket_response
    {
        return $this->getResponse(
            sprintf("HTTP/1.1 200 OK\r\nContent-Encoding: %s\r\n\r\n%s", $encoding, $body),
        )->decompressContent(true);
    }
}
