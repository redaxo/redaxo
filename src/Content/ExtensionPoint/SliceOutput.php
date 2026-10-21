<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleContentBase;
use Redaxo\Core\Content\ArticleSlice;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Output of a slice, in the frontend at runtime also for cached content; in the backend editor it includes the slice
 * panel.
 *
 * @extends ExtensionPoint<string>
 */
final class SliceOutput extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleSlice $slice,
        string $output,
        /** The rendering content object, e.g. for its mode or the slice being edited */
        public readonly ArticleContentBase $content,
    ) {
        parent::__construct(self::class, $output);
    }
}
