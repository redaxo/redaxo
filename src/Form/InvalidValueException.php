<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Exception\Exception;
use Redaxo\Core\Translation\Translatable;
use RuntimeException;

/**
 * Thrown by a field when a submitted or stored value can not be converted into the property type.
 *
 * The message is meant for the user and is shown next to the field.
 */
final class InvalidValueException extends RuntimeException implements Exception
{
    public function __construct(
        public readonly string|Translatable $userMessage,
    ) {
        parent::__construct((string) $userMessage);
    }
}
