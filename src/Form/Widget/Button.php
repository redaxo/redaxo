<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\Backend\Accesskey;
use Redaxo\Core\Form\ButtonStyle;
use Redaxo\Core\Form\SubmitButton;
use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;

/** Submit `<button>` of a {@see SubmitButton}. */
final class Button implements Renderable
{
    use RendersView;

    /** @internal */
    public function __construct(
        public string $name,
        public string $value,
        public string $label,
        public ButtonStyle $style = ButtonStyle::Primary,
        /** Key of {@see Accesskey::$keys}. */
        public ?string $accesskey = null,
        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}
}
