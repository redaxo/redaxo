<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Whether the article is permitted, e.g. to be listed in navigations; extensions return `false` to deny it.
 *
 * @extends ExtensionPoint<bool>
 */
final class ArticlePermissionCheck extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
    ) {
        parent::__construct(self::class, true);
    }
}
