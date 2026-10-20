<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\RendersView;

/**
 * Single-line `<input>`, e.g. of type `text`, `email`, `date` or `time`.
 *
 * Attributes that only apply to some types (`maxlength`, `min`, `max`, `step`, `placeholder` …) are passed via
 * `attributes`.
 */
final class Input implements Widget
{
    use RendersView;

    public function __construct(
        public string $name,
        public ?string $value = null,
        public ?string $id = null,
        public string $type = 'text',
        public bool $required = false,
        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    public function labelTarget(): ?string
    {
        return $this->id;
    }
}
