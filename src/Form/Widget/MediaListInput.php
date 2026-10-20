<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\RendersView;

/**
 * Picker for an ordered list of media via the media pool popup.
 *
 * The file names are submitted comma-separated by a hidden input. The element ids follow the `REX_MEDIALIST_*`
 * convention the media pool scripts rely on.
 */
class MediaListInput implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        /** Only `a-z`, `0-9`, `_` and `-`, as the media pool addresses the inputs by id. */
        public string $id,
        /** @var list<string> */
        public array $fileNames = [],
        /** Media category the media pool opens in. */
        public ?int $categoryId = null,
        /** @var list<string> Allowed file extensions, e.g. `['jpg', 'png']`; empty allows all. */
        public array $types = [],
        /** Whether to show a preview of the selected medium. */
        public bool $preview = false,
    ) {}

    public function labelTarget(): string
    {
        return 'REX_MEDIALIST_SELECT_' . $this->id;
    }
}
