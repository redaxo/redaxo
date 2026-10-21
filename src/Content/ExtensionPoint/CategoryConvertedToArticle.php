<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after a category has been converted into an article of the same id.
 *
 * @extends ExtensionPoint<null>
 */
final class CategoryConvertedToArticle extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly Category $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
