<?php

namespace Redaxo\Core\Content\Exception;

use Redaxo\Core\Exception\Exception;
use Throwable;

final class ArticleNotFoundException extends \Exception implements Exception
{
    public function __construct(?string $message = null, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'Article not found.', 0, $previous);
    }
}
