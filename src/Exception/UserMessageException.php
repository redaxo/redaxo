<?php

namespace Redaxo\Core\Exception;

use Throwable;

/**
 * Exception class for user-friendly error messages.
 */
final class UserMessageException extends \Exception implements Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
