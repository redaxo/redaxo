<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Tab.php
 */
final class Tab extends Fragment
{
    public function __construct(
        /**
         * The tab’s label.
         */
        public string|Fragment $label,

        /**
         * The tab panel’s name.
         */
        public string $name,

        /**
         * The tab panel’s content.
         */
        public string|Fragment $panel,

        /**
         * Draws the tab in an active state.
         */
        public bool $active = false,

        /**
         * Makes the tab closable and
         * shows a close button.
         */
        public bool $closable = false,

        /**
         * Disables the tab and prevent selection.
         */
        public bool $disabled = false,

        public HtmlAttributes $tabAttributes = new HtmlAttributes(),

        public HtmlAttributes $panelAttributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Tab.php';
    }
}
