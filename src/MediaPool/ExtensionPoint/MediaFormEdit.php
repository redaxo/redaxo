<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Extra fields in the media edit form.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaFormEdit extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
    ) {
        parent::__construct(self::class, '');
    }
}
