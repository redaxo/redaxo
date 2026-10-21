<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Whether the medium is permitted, e.g. to be delivered; extensions return `false` to deny it.
 *
 * @extends ExtensionPoint<bool>
 */
final class MediaPermissionCheck extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
    ) {
        parent::__construct(self::class, true);
    }
}
