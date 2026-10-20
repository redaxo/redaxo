<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\RendersView;

/** Single-line `<input>`, e.g. of type `text`, `email`, `date` or `time`. */
class Input implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        public ?string $value = null,
        public ?string $id = null,
        public string $type = 'text',
        /** @var positive-int|null */
        public ?int $maxLength = null,
        public bool $required = false,
        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    public function labelTarget(): ?string
    {
        return $this->id;
    }
}
