<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * URL of a medium; extensions return their URL, `null` keeps the default URL in the media folder.
 *
 * @extends ExtensionPoint<string|null>
 */
final class MediaUrl extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
    ) {
        parent::__construct(self::class, null);
    }
}
