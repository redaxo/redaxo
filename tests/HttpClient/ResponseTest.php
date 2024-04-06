<?php

namespace Redaxo\Core\Tests\HttpClient;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redaxo\Core\HttpClient\Response;
use rex_socket_exception;

use const ZLIB_ENCODING_DEFLATE;
use const ZLIB_ENCODING_GZIP;

/** @internal */
final class ResponseTest extends TestCase
{
    private function getResponse(string $content): Response
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        fseek($stream, 0);

        return new Response($stream);
    }

    /** @return list<array{string}> */
    public static function getInvalidHeader(): array
    {
        return [
            [''],
            ['abc'],
            ['200 OK'],
        ];
    }

    #[DataProvider('getInvalidHeader')]
    public function testConstructWithInvalidHeader(string $header): void
    {
        $this->expectException(rex_socket_exception::class);
        $this->expectExceptionMessage('Missing status code in response header');

        $this->getResponse($header . "\r\n");
    }

    /** @return list<array{string, ?int, ?string, string}> */
    public static function getStatusProvider(): array
    {
        return [
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

        self::assertSame($statusCode, $response->getStatusCode(), 'getStatusCode()');
        self::assertSame($statusMessage, $response->getStatusMessage(), 'getStatusMessage()');
        self::assertSame(200 == $statusCode, $response->isOk(), 'isOk()');

        $methods = ['isInformational', 'isSuccessful', 'isRedirection', 'isClientError', 'isServerError', 'isInvalid'];
        foreach ($methods as $method) {
            self::assertSame($positiveMethod == $method, $response->$method(), $method . '()');
        }
    }

    public function testGetHeader(): void
    {
        $header = "HTTP/1.1 200 OK\r\nKey1: Value1\r\nkey2: Value2";
        $response = $this->getResponse($header . "\r\n\r\nbody\r\nbody");

        self::assertSame($header, $response->getHeader(), 'getHeader() without params returns full header');
        self::assertSame('Value1', $response->getHeader('Key1'), 'getHeader($key) returns the value of the key');
        self::assertSame('Value2', $response->getHeader('Key2'), 'getHeader($key) returns the value of the key');
        self::assertNull($response->getHeader('Key3'), 'getHeader($key) returns null for non-existing keys');
    }

    public function testGetBody(): void
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        self::assertSame($body, $response->getBody());
    }

    public function testWriteBodyTo(): void
    {
        $body = "body1\r\nbody2";
        $response = $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\n" . $body);

        $temp = fopen('php://temp', 'r+');
        $response->writeBodyTo($temp);
        fseek($temp, 0);
        self::assertSame($body, fread($temp, 1024));
        fclose($temp);
    }

    public function testGetBodyWithEncoding(): void
    {
        $body = "This is the\r\noriginal content";

        self::assertSame($body, $this->createResponseWithEncoding('gzip',
            zlib_encode($body, ZLIB_ENCODING_GZIP), )->getBody());

        self::assertSame($body, $this->createResponseWithEncoding('deflate',
            zlib_encode($body, ZLIB_ENCODING_DEFLATE), )->getBody());

        // Test combination with chunked with real responses from the redaxo webservice
        $decodedResponseContent =
            file_get_contents(__DIR__ . '/ResponseTestFiles/response_decoded');

        self::assertSame($decodedResponseContent, $this->getResponse(
            file_get_contents(__DIR__ . '/ResponseTestFiles/response_chunked.testresp'),
        )->decompressContent(true)->getBody());

        self::assertSame($decodedResponseContent, $this->getResponse(
            file_get_contents(__DIR__ . '/ResponseTestFiles/response_chunked_gzip.testresp'),
        )->decompressContent(true)->getBody());
    }

    public function testEncodingHeader(): void
    {
        self::assertCount(0, $this->getResponse("HTTP/1.1 200 OK\r\nKey: Value\r\n\r\nTest")
            ->getContentEncodings(), );

        self::assertSame(['gzip', 'deflate'], $this->createResponseWithEncoding('gzip, deflate', 'test')
            ->getContentEncodings(), );

        self::assertSame(['gzip'], $this->createResponseWithEncoding('gzip', 'test')
            ->getContentEncodings(), );
    }

    private function createResponseWithEncoding(string $encoding, string $body): Response
    {
        return $this->getResponse(
            sprintf("HTTP/1.1 200 OK\r\nContent-Encoding: %s\r\n\r\n%s", $encoding, $body),
        )->decompressContent(true);
    }
}
