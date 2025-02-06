<?php

namespace Redaxo\Core\Util\Exception;

use Redaxo\Core\Exception\Exception;
use RuntimeException;
use Throwable;

/**
 * Exception class for yaml parse errors.
 */
final class YamlParseException extends RuntimeException implements Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
