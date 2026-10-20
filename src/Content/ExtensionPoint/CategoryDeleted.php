<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched once the category and its start article are gone, carries its state before the deletion.
 *
 * @extends ExtensionPoint<null>
 */
final class CategoryDeleted extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
