<?php

namespace Redaxo\Core\MediaManager\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * URL of a file processed by a media type.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaManagerUrl extends ExtensionPoint
{
    public function __construct(
        string $url,
        public readonly string $type,
        public readonly string $fileName,
        /** Cache buster of the URL, changing with the file and the type definition */
        public readonly ?string $buster,
    ) {
        parent::__construct(self::class, $url);
    }
}
