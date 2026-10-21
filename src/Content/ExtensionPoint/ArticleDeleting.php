<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched before an article is deleted, for a category before its start article is deleted; the slices still exist.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleDeleting extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
