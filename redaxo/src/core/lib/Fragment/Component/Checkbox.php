<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Slot;
use rex_fragment;

class Checkbox extends rex_fragment
{
    public function __construct(
        /**
         * The checkbox's label. Alternatively, you can use the
         * label attribute.
         */
        public ?Slot $slotLabel = null,

        /**
         * The checkbox's label. If you need to display HTML,
         * use the label slot instead.
         */
        public ?string $label = null,

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

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {
        parent::__construct([]);
    }

    public function render(): string
    {
        return parent::parse('core/Component/Checkbox.php');
    }
}
