<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Whether the category is permitted, e.g. to be listed in navigations; extensions return `false` to deny it.
 *
 * @extends ExtensionPoint<bool>
 */
final class CategoryPermissionCheck extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
    ) {
        parent::__construct(self::class, true);
    }
}
