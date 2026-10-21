<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * SQL `ORDER BY` clause of the article list in the backend structure page.
 *
 * @extends ExtensionPoint<string>
 */
final class ArticleListOrder extends ExtensionPoint
{
    public function __construct(
        /** `null` for the root level */
        public readonly ?Category $category,
        public readonly int $languageId,
    ) {
        parent::__construct(self::class, 'priority, name');
    }
}
