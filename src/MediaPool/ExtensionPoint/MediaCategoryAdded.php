<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\MediaCategory;

/** @extends ExtensionPoint<null> */
final class MediaCategoryAdded extends ExtensionPoint
{
    public function __construct(
        public readonly MediaCategory $category,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
