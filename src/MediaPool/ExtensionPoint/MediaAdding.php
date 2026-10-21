<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;

/**
 * Dispatched before a file is added to the media pool; returning an error message rejects it.
 *
 * Once an extension has rejected the file, later extensions should keep the message.
 *
 * @extends ExtensionPoint<string|null>
 */
final class MediaAdding extends ExtensionPoint
{
    public function __construct(
        /** Name of the file in the media pool, possibly adjusted to be unique */
        public readonly string $fileName,
        public readonly string $originalFileName,
        /** Path of the file to be added */
        public readonly string $path,
        public readonly ?string $type,
        public readonly string $title,
        public readonly ?int $categoryId,
        public readonly bool $isUpload,
    ) {
        parent::__construct(self::class, null);
    }
}
