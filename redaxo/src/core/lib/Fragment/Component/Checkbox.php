<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Checkbox.php
 */
final class Checkbox extends Fragment
{
    public function __construct(
        /**
         * The checkbox's label.
         */
        public string|Fragment|null $label = null,

        /**
         * The name of the checkbox, submitted as a name/value
         * pair with form data.
         */
        public ?string $name = null,

        /**
         * The current value of the input, submitted as a
         * name/value pair with form data.
         */
        public ?string $value = null,

        /**
         * Draws the checkbox in a checked state.
         */
        public bool $checked = false,

        /**
         * Draws the checkbox in an indeterminate state.
         * This is usually applied to checkboxes that
         * represents a "select all/none" behavior when
         * associated checkboxes have a mix of checked and
         * unchecked states.
         */
        public bool $indeterminate = false,

        /**
         * Disables the checkbox.
         */
        public bool $disabled = false,

        /**
         * Makes the checkbox a required field.
         */
        public bool $required = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Checkbox.php';
    }
}
