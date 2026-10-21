<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Rendered content of an article, of the given content section or of all of them.
 *
 * @extends ExtensionPoint<string>
 */
final class ArticleContentOutput extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleContent $content,
        public readonly ?int $contentSectionId,
        string $output,
    ) {
        parent::__construct(self::class, $output);
    }
}
