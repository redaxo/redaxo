<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Dispatched after a medium has been updated in one language, both objects are in that language.
 *
 * @extends ExtensionPoint<null>
 */
final class MediaUpdated extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
        public readonly Media $previous,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
