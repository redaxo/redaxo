<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Enum\Size;
use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Switcher.php
 */
final class Switcher extends Fragment
{
    public function __construct(
        /**
         * The switch's label.
         */
        public string $label,

        /**
         * The name of the switch, submitted as a name/value
         * pair with form data.
         */
        public ?string $name = null,

        /**
         * The current value of the switch, submitted as a
         * name/value pair with form data.
         */
        public ?string $value = null,

        /**
         * The switch's size.
         */
        public ?Size $size = null,

        /**
         * Disables the switch.
         */
        public bool $disabled = false,

        /**
         * Draws the switch in a checked state.
         */
        public bool $checked = false,

        /**
         * Makes the switch a required field.
         */
        public bool $required = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Switcher.php';
    }
}
