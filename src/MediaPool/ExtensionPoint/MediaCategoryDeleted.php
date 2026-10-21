<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\MediaCategory;

/**
 * Dispatched once the media category is gone, carries its state before the deletion.
 *
 * @extends ExtensionPoint<null>
 */
final class MediaCategoryDeleted extends ExtensionPoint
{
    public function __construct(
        public readonly MediaCategory $category,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
