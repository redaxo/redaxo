<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/MenuLabel.php
 */
final class MenuLabel extends Fragment
{
    public function __construct(
        /**
         * The menu label’s content.
         */
        public string|Fragment $label,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/MenuLabel.php';
    }
}
