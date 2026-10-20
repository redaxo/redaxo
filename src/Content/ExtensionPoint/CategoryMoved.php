<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after a category has been moved into another category, `previous` still carries the former parent.
 *
 * @extends ExtensionPoint<null>
 */
final class CategoryMoved extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
        public readonly Category $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
