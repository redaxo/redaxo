<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched before the slices of `source` are copied into `article`, both in their respective language.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleSlicesCopying extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly Article $source,
        /** Revision to copy, `null` for all revisions */
        public readonly ?int $revision,
        /** Whether the existing slices of the target are deleted first */
        public readonly bool $overwrite,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
