<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Dialog.php
 */
final class Dialog extends Fragment
{
    public function __construct(
        /**
         * The dialog's main content.
         */
        public string|Fragment $body,

        /**
         * The dialog’s label.
         */
        public string|Fragment|null $label = null,

        /**
         * The button to open the dialog.
         */
        public ?Fragment $button = null,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Dialog.php';
    }
}
