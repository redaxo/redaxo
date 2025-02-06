<?php

namespace Redaxo\Core\Http\Exception;

use Redaxo\Core\Exception\Exception;
use RuntimeException;
use Throwable;

use function is_string;

/**
 * Exception class for http-status code handling.
 */
final class HttpException extends RuntimeException implements Exception
{
    private string $httpCode;

    public function __construct(string|Throwable $cause, string $httpCode)
    {
        parent::__construct(is_string($cause) ? $cause : $cause->getMessage(), 0, $cause instanceof Throwable ? $cause : null);

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
