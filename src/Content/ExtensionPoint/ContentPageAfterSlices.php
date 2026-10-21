<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Output below the slices in the backend content pages.
 *
 * @extends ExtensionPoint<string>
 */
final class ContentPageAfterSlices extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        /** The current backend page, e.g. `content/edit` */
        public readonly string $page,
        public readonly int $contentSectionId,
        /** The slice function in progress: `add`, `edit` or `delete`, empty if none */
        public readonly string $function,
        /** The slice the function applies to */
        public readonly int $sliceId,
        public readonly int $sliceRevision,
    ) {
        parent::__construct(self::class, '');
    }
}
