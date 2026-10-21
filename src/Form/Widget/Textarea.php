<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\RendersView;

/** Multi-line text input. */
final class Textarea implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        public ?string $value = null,
        public ?string $id = null,
        /** @var positive-int */
        public int $rows = 5,
        public bool $required = false,
        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    public function labelTarget(): ?string
    {
        return $this->id;
    }
}
