<?php

namespace Redaxo\Core\MediaManager\Exception;

use Redaxo\Core\Exception\Exception;
use Throwable;

final class MediaNotFoundException extends \Exception implements Exception
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'Media not found.', 0, $previous);
    }
}
