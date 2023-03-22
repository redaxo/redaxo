<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Drawer.php
 */
final class Drawer extends Fragment
{
    public function __construct(
        /**
         * The drawer's main content.
         */
        public string|Fragment $body,

        /**
         * An optional header for the drawer.
         */
        public string|Fragment|null $header = null,

        /**
         * The drawer's footer, usually one or
         * more buttons representing various options.
         */
        public string|Fragment|null $footer = null,

        /**
         * The drawer's label as displayed in the header.
         * You should always include a relevant label even
         * when using no-header, as it is required for
         * proper accessibility.
         */
        public string|Fragment|null $label = null,

        /**
         * Indicates whether or not the drawer
         * is open. You can toggle this attribute
         * to show and hide the drawer, or you can
         * use the show() and hide() methods and
         * this attribute will reflect the drawer's
         * open state.
         */
        public bool $open = false,

        /**
         * The direction from which the drawer will open.
         */
        public ?DrawerPlacement $placement = null,

        /**
         * By default, the drawer slides out of its
         * containing block (usually the viewport).
         * To make the drawer slide out of its parent
         * element, set this attribute and add
         * position: relative to the parent.
         */
        public bool $contained = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Drawer.php';
    }
}

enum DrawerPlacement: string
{
    case Bottom = 'bottom';
    case End = 'end';
    case Start = 'start';
    case Top = 'top';
}
