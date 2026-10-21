<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Category;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after the status of a category has been changed in one language, both objects are in that language.
 *
 * @extends ExtensionPoint<null>
 */
final class CategoryStatusChanged extends ExtensionPoint
{
    public function __construct(
        public readonly Category $category,
        public readonly Category $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
