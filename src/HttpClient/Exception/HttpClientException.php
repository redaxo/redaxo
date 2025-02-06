<?php

namespace Redaxo\Core\HttpClient\Exception;

use Redaxo\Core\Exception\Exception;
use Throwable;

/**
 * @see Request
 */
final class HttpClientException extends \Exception implements Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
