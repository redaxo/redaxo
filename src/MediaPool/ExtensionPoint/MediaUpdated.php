<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/** @extends ExtensionPoint<null> */
final class MediaUpdated extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
