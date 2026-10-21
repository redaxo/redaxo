<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\Media;

/**
 * Functions of a medium in the media list, initially the link to select it for the opener field.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaListFunctions extends ExtensionPoint
{
    public function __construct(
        public readonly Media $media,
        string $functions,
    ) {
        parent::__construct(self::class, $functions);
    }
}
