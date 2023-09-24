<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Divider.php
 */
final class Divider extends Fragment
{
    public function __construct(
        /**
         * Draws the divider in a vertical orientation.
         *
         * The divider will span the full height of its
         * container. Vertical dividers work especially
         * well inside of a flex container.
         */
        public bool $vertical = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Divider.php';
    }
}
