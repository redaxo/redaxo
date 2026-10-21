<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched after an article has been moved into another category, `previous` still carries the former category.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleMoved extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly Article $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
