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
         * The tab's label.
         */
        public string|Fragment $label,

        /**
         * The tab's main content.
         */
        public string|Fragment $body,

        /**
         * Disables the tab.
         */
        public bool $disabled = false,

        /**
         * Activate the tab.
         */
        public bool $active = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Tab.php';
    }
}
