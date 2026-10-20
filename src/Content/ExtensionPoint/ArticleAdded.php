<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/** @extends ExtensionPoint<null> */
final class ArticleAdded extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
