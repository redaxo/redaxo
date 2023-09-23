<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Badge.php
 */
final class Badge extends Fragment
{
    public function __construct(
        /**
         * The badge’s content.
         */
        public string|Fragment $body,

        /**
         * The badge’s theme variant.
         */
        public BadgeVariant $variant = BadgeVariant::Primary,

        /**
         * Draws a pill-style badge with rounded edges.
         */
        public bool $pill = false,

        /**
         * Makes the badge pulsate to draw attention.
         */
        public bool $pulse = false,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Badge.php';
    }
}

enum BadgeVariant: string
{
    case Danger = 'danger';
    case Neutral = 'neutral';
    case Primary = 'primary';
    case Success = 'success';
    case Warning = 'warning';
}
