<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Menu.php
 */
final class Menu extends Fragment
{
    public function __construct(
        /**
         * The menu’s content, including menu
         * items, menu labels, and dividers.
         *
         * @var list<MenuItem|MenuLabel>
         */
        public array $elements,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Menu.php';
    }
}
