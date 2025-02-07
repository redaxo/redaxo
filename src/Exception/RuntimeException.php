<?php

namespace Redaxo\Core\Exception;

use Throwable;

class RuntimeException extends \RuntimeException implements Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
