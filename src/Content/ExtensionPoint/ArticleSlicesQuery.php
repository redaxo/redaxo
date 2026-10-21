<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleContentBase;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * SQL query selecting the slices to render, e.g. to read them from the history instead.
 *
 * @extends ExtensionPoint<string>
 */
final class ArticleSlicesQuery extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleContentBase $content,
        string $query,
    ) {
        parent::__construct(self::class, $query);
    }
}
