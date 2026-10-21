<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched before a slice is added to the article (in the language of the article object).
 *
 * @extends ExtensionPoint<null>
 */
final class SliceAdding extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly int $contentSectionId,
        public readonly string $moduleKey,
        public readonly int $revision,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
