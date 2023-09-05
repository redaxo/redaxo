<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Details.php
 */
final class Details extends Fragment
{
    public function __construct(
        /**
         * The details’ summary.
         */
        public string|Fragment $summary,
        /**
         * The details' main content.
         */
        public string|Fragment $body,

        /**
         * Indicates whether or not the details is open.
         */
        public bool $open = false,

        /**
         * Disables the details so it can’t be toggled.
         */
        public bool $disabled = false,

        /**
         * Optional expand icon to use instead of the default.
         */
        public ?Fragment $expandIcon = null,

        /**
         * Optional collapse icon to use instead of the default.
         */
        public ?Fragment $collapseIcon = null,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Details.php';
    }
}
