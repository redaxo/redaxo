<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after the status of an article has been changed in one language, both objects are in that language.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleStatusChanged extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly Article $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
