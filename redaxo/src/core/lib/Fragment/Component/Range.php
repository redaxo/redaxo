<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Range.php
 */
final class Range extends Fragment
{
    public function __construct(
        /**
         * The range's label.
         */
        public string|Fragment|null $label = null,

        /**
         * Text that describes how to use the range.
         */
        public string|Fragment|null $notice = null,

        /**
         * The name of the range, submitted as a name/value
         * pair with form data.
         */
        public ?string $name = null,

        /**
         * The current value of the range, submitted as a
         * name/value pair with form data.
         *
         * @var null|int|numeric-string
         */
        public null|int|string $value = null,

        /**
         * Disables the range.
         */
        public bool $disabled = false,

        /**
         * The minimum acceptable value of the range.
         *
         * @var null|int|numeric-string
         */
        public null|int|string $min = null,

        /**
         * The maximum acceptable value of the range.
         *
         * @var null|int|numeric-string
         */
        public null|int|string $max = null,

        /**
         * The interval at which the range will
         * increase and decrease.
         *
         * @var null|int|numeric-string
         */
        public null|int|string $step = null,

        /**
         * The preferred placement of the range’s tooltip.
         */
        public RangeTooltipPlacement $tooltipPlacement = RangeTooltipPlacement::Top,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Range.php';
    }
}

enum RangeTooltipPlacement: string
{
    case Bottom = 'bottom';
    case Top = 'top';
}
