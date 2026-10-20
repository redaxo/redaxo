<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;

/**
 * Checkbox together with its label.
 *
 * The label belongs to the control (it is its click target), so unlike other widgets the checkbox renders it itself.
 */
class CheckboxInput implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        public string|Renderable $label,
        public bool $checked = false,
        public string $value = '1',
        public ?string $id = null,
        public bool $required = false,
        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    public function labelTarget(): ?string
    {
        return null;
    }
}
