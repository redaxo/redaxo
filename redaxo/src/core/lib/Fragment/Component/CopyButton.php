<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/CopyButton.php
 */
final class CopyButton extends Fragment
{
    public function __construct(
        /**
         * The text value to copy.
         */
        public ?string $value = null,

        /**
         * An id that references an element in the same document
         * from which data will be copied. If both this and
         * value are present, this value will take precedence.
         * By default, the target element’s textContent will
         * be copied. To copy an attribute, append the attribute
         * name wrapped in square brackets, e.g. from="el[value]".
         * To copy a property, append a dot and the property
         * name, e.g. from="el.value".
         */
        public ?string $from = null,

        /**
         * The icon to show in the default copy state.
         */
        public ?Fragment $copyIcon = null,

        /**
         * The icon to show when the content is copied.
         */
        public ?Fragment $successIcon = null,

        /**
         * The icon to show when a copy error occurs.
         */
        public ?Fragment $errorIcon = null,

        /**
         * A custom label to show in the tooltip.
         */
        public ?string $copyLabel = null,

        /**
         * A custom label to show in the tooltip after copying.
         */
        public ?string $successLabel = null,

        /**
         * A custom label to show in the tooltip when a copy error occurs.
         */
        public ?string $errorLabel = null,

        /**
         * Disables the button.
         */
        public bool $disabled = false,

        /**
         * The preferred placement of the tooltip.
         */
        public CopyButtonTooltipPlacement $tooltipPlacement = CopyButtonTooltipPlacement::Top,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/CopyButton.php';
    }
}

enum CopyButtonTooltipPlacement: string
{
    case Top = 'top';
    case Right = 'right';
    case Bottom = 'bottom';
    case Left = 'left';
}
