<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after an article has been converted into a category of the same id.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleConvertedToCategory extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
        public readonly Article $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
