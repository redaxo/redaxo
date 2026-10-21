<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleSlice;
use Redaxo\Core\Content\SliceMoveDirection;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched before a slice is moved one position up or down.
 *
 * @extends ExtensionPoint<null>
 */
final class SliceMoving extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleSlice $slice,
        public readonly SliceMoveDirection $direction,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
