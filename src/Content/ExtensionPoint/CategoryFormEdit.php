<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Extra table rows below the row of the category edit form.
 *
 * @extends ExtensionPoint<string>
 */
final class CategoryFormEdit extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
        /** Number of table columns */
        public readonly int $colspan,
    ) {
        parent::__construct(self::class, '');
    }
}
