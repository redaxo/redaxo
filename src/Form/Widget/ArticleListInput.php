<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\RendersView;

/**
 * Picker for an ordered list of articles via the linkmap popup.
 *
 * The article ids are submitted comma-separated by a hidden input. The element ids follow the `REX_LINKLIST_*`
 * convention the linkmap scripts rely on.
 */
final class ArticleListInput implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        /** Only `a-z`, `0-9`, `_` and `-`, as the linkmap addresses the inputs by id. */
        public string $id,
        /** @var list<int> */
        public array $articleIds = [],
        /** Category the linkmap opens in; defaults to the current category. */
        public ?int $categoryId = null,
        public bool $disabled = false,
    ) {}

    public function labelTarget(): string
    {
        return 'REX_LINKLIST_SELECT_' . $this->id;
    }
}
