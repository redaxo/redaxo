<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Extra table rows below the row of the category add form.
 *
 * @extends ExtensionPoint<string>
 */
final class CategoryFormAdd extends ExtensionPoint
{
    public function __construct(
        /** `null` for the root level */
        public readonly ?Category $parent,
        public readonly int $languageId,
        /** Number of table columns */
        public readonly int $colspan,
    ) {
        parent::__construct(self::class, '');
    }
}
