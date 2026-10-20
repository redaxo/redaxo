<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after an article has been copied, `article` is the new copy of `source`.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleCopied extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly Article $source,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
