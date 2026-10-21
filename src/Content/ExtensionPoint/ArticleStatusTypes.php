<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * The status types of articles, which apply to categories as well, as a category has the status of its start article.
 *
 * Each type is a list of label, CSS class and icon, indexed by the status value.
 *
 * @extends ExtensionPoint<list<array{string, string, string}>>
 */
final class ArticleStatusTypes extends ExtensionPoint
{
    /** @param list<array{string, string, string}> $types */
    public function __construct(array $types)
    {
        parent::__construct(self::class, $types);
    }
}
