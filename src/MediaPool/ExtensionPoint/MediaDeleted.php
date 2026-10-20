<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Dispatched once the medium and its file are gone, carries its state before the deletion.
 *
 * @extends ExtensionPoint<null>
 */
final class MediaDeleted extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
    ) {
        parent::__construct(self::class, null, readonly: true);
    }
}
