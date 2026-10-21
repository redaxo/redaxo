<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Preview of a medium in the media list, e.g. to show a player for media the media manager can't preview.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaListThumbnail extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
        string $thumbnail,
    ) {
        parent::__construct(self::class, $thumbnail);
    }
}
