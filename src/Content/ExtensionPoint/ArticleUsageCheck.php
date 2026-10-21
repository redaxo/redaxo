<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\Article;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Util\UsageList;

/**
 * Dispatched to find the places using the article, e.g. before it is deleted; extensions add theirs to `$usages`.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleUsageCheck extends ExtensionPoint
{
    public function __construct(
        public readonly Article $article,
        public readonly UsageList $usages,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
