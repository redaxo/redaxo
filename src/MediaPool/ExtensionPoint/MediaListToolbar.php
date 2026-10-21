<?php

namespace Redaxo\Core\MediaPool\ExtensionPoint;

use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\MediaPool\MediaCategory;

/**
 * Toolbar above the media list and the media detail page, initially containing the category selection and search.
 *
 * @extends ExtensionPoint<string>
 */
final class MediaListToolbar extends ExtensionPoint
{
    public function __construct(
        /** The current subpage of the media pool, e.g. `media` */
        public readonly ?string $subpage,
        /** `null` for the root level */
        public readonly ?MediaCategory $category,
        string $toolbar,
    ) {
        parent::__construct(self::class, $toolbar);
    }
}
