<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Sidebar of the media detail page, initially containing the preview.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaDetailSidebar extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
        string $sidebar,
    ) {
        parent::__construct(self::class, $sidebar);
    }
}
