<?php

namespace Redaxo\Core\Http\Exception;

use Redaxo\Core\Exception\Exception;
use RuntimeException;
use Throwable;

/**
 * Exception class for http-status code handling.
 */
final class HttpException extends RuntimeException implements Exception
{
    private string $httpCode;

    public function __construct(Throwable $cause, string $httpCode)
    {
        parent::__construct($cause->getMessage(), 0, $cause);

        $this->httpCode = $httpCode;
    }

    public function getHttpCode(): string
    {
        return $this->httpCode;
    }

    public function isClientError(): bool
    {
        return str_starts_with($this->httpCode, '4');
    }
}
