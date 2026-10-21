<?php

namespace Redaxo\Core\Content\ExtensionPoint;

use Redaxo\Core\Content\ArticleContent;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched in the frontend before the requested article is rendered, to configure its content object, e.g. to
 * render the history or working version live from the database instead of the content cache.
 *
 * @extends ExtensionPoint<null>
 */
final class ArticleContentRendering extends ExtensionPoint
{
    public function __construct(
        public readonly ArticleContent $content,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
