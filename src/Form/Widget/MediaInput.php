<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\RendersView;

/**
 * Picker for one medium via the media pool popup.
 *
 * The file name is submitted by the (read-only) text input. The element id follows the `REX_MEDIA_*` convention the
 * media pool scripts rely on.
 */
final class MediaInput implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        /** Only `a-z`, `0-9`, `_` and `-`, as the media pool addresses the input by id. */
        public string $id,
        public ?string $fileName = null,
        /** Media category the media pool opens in. */
        public ?int $categoryId = null,
        /** @var list<string> Allowed file extensions, e.g. `['jpg', 'png']`; empty allows all. */
        public array $types = [],
        /** Whether to show a preview of the selected medium. */
        public bool $preview = false,
        public bool $disabled = false,
    ) {}

    public function labelTarget(): string
    {
        return 'REX_MEDIA_' . $this->id;
    }
}
