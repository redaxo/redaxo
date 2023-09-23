<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Tooltip.php
 */
final class Tooltip extends Fragment
{
    public function __construct(
        /**
         * The tooltip's main content.
         */
        public string|Fragment $body,

        /**
         * The tooltip’s content.
         */
        public string|Fragment $content,

        /**
         * The preferred placement of the tooltip.
         */
        public TooltipPlacement $placement = TooltipPlacement::Top,

        /**
         * Disables the tooltip so it won’t show
         * when triggered.
         */
        public bool $disabled = false,

        /**
         * The distance in pixels from which to
         * offset the tooltip away from its target.
         */
        public ?int $distance = null,

        /**
         * Indicates whether or not the tooltip
         * is open. You can use this in lieu of
         * the show/hide methods.
         */
        public bool $open = false,

        /**
         * The distance in pixels from which to
         * offset the tooltip along its target.
         */
        public ?int $skidding = null,

        /**
         * Controls how the tooltip is activated.
         * When manual is used, the tooltip must
         * be activated programmatically.
         *
         * @var list<TooltipTrigger>
         */
        public array|TooltipTrigger $trigger = [TooltipTrigger::Hover, TooltipTrigger::Focus],

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Tooltip.php';
    }
}

enum TooltipPlacement: string
{
    case Bottom = 'bottom';
    case BottomEnd = 'bottom-end';
    case BottomStart = 'bottom-start';
    case Left = 'left';
    case LeftEnd = 'left-end';
    case LeftStart = 'left-start';
    case Right = 'right';
    case RightEnd = 'right-end';
    case RightStart = 'right-start';
    case Top = 'top';
    case TopEnd = 'top-end';
    case TopStart = 'top-start';
}

enum TooltipTrigger: string
{
    case Click = 'click';
    case Focus = 'focus';
    case Hover = 'hover';
    case Manual = 'manual';
}
