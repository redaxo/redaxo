<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleSlice;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Preview of a slice in the backend content editor, e.g. to show something else than the frontend output.
 *
 * @extends ExtensionPoint<string>
 */
final class SlicePreview extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleSlice $slice,
        string $preview,
    ) {
        parent::__construct(self::class, $preview);
    }
}
