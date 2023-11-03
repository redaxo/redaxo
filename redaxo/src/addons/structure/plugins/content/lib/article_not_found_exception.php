<?php

/**
 * @package redaxo\structure\content
 */
class rex_article_not_found_exception extends rex_exception
{
    public function __construct(?string $message = null, ?Exception $previous = null)
    {
        parent::__construct($message ?? 'Article not found.', $previous);
    }
}
