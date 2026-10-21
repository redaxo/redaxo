<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Module selection for adding a slice in the backend content editor.
 *
 * @extends ExtensionPoint<string>
 */
final class ContentPageModuleSelect extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly ?int $contentSectionId,
        /** The slice the new slice is added before, `-1` at the end */
        public readonly int $sliceId,
        string $select,
    ) {
        parent::__construct(self::class, $select);
    }
}
