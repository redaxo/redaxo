<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/** @extends ExtensionPoint<null> */
final class CategoryAdded extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
