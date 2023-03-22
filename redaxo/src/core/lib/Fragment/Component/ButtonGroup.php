<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/ButtonGroup.php
 */
final class ButtonGroup extends Fragment
{
    public function __construct(
        /**
         * The button groups main content.
         */
        public string|Fragment $body,

        /**
         * A label to use for the button group.
         * This won't be displayed on the screen,
         * but it will be announced by assistive
         * devices when interacting with the control
         * and is strongly recommended.
         */
        public null|string $label = null,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/ButtonGroup.php';
    }
}
