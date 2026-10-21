<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Extra buttons in the category add and edit forms.
 *
 * @extends ExtensionPoint<string>
 */
final class CategoryFormButtons extends ExtensionPoint
{
    public function __construct(
        /** The edited category, `null` in the add form */
        public readonly ?Category $category,
        /** `null` for the root level */
        public readonly ?Category $parent,
        public readonly int $languageId,
    ) {
        parent::__construct(self::class, '');
    }
}
