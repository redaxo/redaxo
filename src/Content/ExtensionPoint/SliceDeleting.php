<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleSlice;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched before a slice is deleted.
 *
 * @extends ExtensionPoint<null>
 */
final class SliceDeleting extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleSlice $slice,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
