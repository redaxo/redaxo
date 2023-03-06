<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Fragment;

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

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {}

    protected function getPath(): string
    {
        return 'core/Component/Card.php';
    }
}