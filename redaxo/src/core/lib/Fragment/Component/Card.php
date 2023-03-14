<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

/**
 * @see redaxo/src/core/fragments/core/Component/Card.php
 */
class Card extends Fragment
{
    public function __construct(
        /**
         * The card's main content.
         */
        public string|Fragment $body,

        /**
         * An optional header for the card.
         */
        public string|Fragment|null $header = null,

        /**
         * An optional footer for the card.
         */
        public string|Fragment|null $footer = null,

        /**
         * An optional image to render at the start of
         * the card.
         */
        public ?Fragment $image = null,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Card.php';
    }
}
