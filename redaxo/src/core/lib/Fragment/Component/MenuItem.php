<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/MenuItem.php
 */
final class MenuItem extends Fragment
{
    public function __construct(
        /**
         * The menu item’s label.
         */
        public string|Fragment $label,

        /**
         * Used to prepend an icon or similar element to the menu item.
         */
        public string|Fragment|null $prefix = null,

        /**
         * Used to append an icon or similar element to the menu item.
         */
        public string|Fragment|null $suffix = null,

        /**
         * Used to denote a nested menu.
         */
        public string|Fragment|null $submenu = null,

        /**
         * The type of menu item to render. To use checked,
         * this value must be set to checkbox.
         */
        public MenuItemType $type = MenuItemType::Normal,

        /**
         * Draws the item in a checked state.
         */
        public bool $checked = false,

        /**
         * A unique value to store in the menu item.
         * This can be used as a way to identify menu
         * items when selected.
         */
        public ?string $value = null,

        /**
         * Draws the menu item in a disabled
         * state, preventing selection.
         */
        public bool $disabled = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/MenuItem.php';
    }
}

enum MenuItemType: string
{
    case Checkbox = 'checkbox';
    case Normal = 'normal';
}
