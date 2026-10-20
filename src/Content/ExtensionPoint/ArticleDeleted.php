<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched once the article is gone, carries its state before the deletion.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleDeleted extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
