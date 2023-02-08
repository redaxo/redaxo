<?php

namespace Redaxo\Core\Fragment\Component;

use Redaxo\Core\Fragment\Slot;
use rex_fragment;

class Card extends rex_fragment
{
    private string $fileName = 'core/Component/Card.php';

    public function __construct(
        /**
         * The card's main content.
         */
        public Slot $slotDefault,

        /**
         * An optional header for the card.
         */
        public ?Slot $slotHeader = null,

        /**
         * An optional footer for the card.
         */
        public ?Slot $slotFooter = null,

        /**
         * An optional image to render at the start of
         * the card.
         */
        public ?Slot $slotImage = null,

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {
        parent::__construct([]);
    }

    public function render(): string
    {
        return parent::parse($this->fileName);
    }
}
