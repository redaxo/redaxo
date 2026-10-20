<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after an article has become the start article of its category: the category continues under its id,
 * while the former start article remains as a plain article inside it.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleConvertedToStartArticle extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly Article $previousStartArticle,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
