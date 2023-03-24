<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/TabGroup.php
 */
final class TabGroup extends Fragment
{
    public function __construct(
        /**
         * The tabs.
         *
         * @var list<Fragment>
         */
        public array|Fragment $tabs,

        /**
         * The placement of the tabs.
         */
        public TabGroupPlacement $placement = TabGroupPlacement::Top,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/TabGroup.php';
    }
}

enum TabGroupPlacement: string
{
    case Bottom = 'bottom';
    case End = 'end';
    case Start = 'start';
    case Top = 'top';
}
