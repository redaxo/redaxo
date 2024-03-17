<?php

namespace Redaxo\Core\Exception;

use Throwable;

final class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
    /** @pure */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        /** @psalm-suppress ImpureMethodCall */
        parent::__construct($message, 0, $previous);
    }
}
