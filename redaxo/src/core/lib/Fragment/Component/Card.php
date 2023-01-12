<?php

namespace Redaxo\Core\Fragment\Component;

use Fragment\Slot;
use rex_fragment;

class Card extends rex_fragment
{
    private string $fileName = 'core/Component/Card.php';

    public function __construct(
        public Slot $slotDefault,
        public ?Slot $slotHeader = null,
        public ?Slot $slotFooter = null,
        public ?Slot $slotImage = null,

        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
        parent::__construct([]);
    }

    public function parse($filename = null): string
    {
        if (!$filename) {
            $filename = $this->fileName;
        }
        return parent::parse($filename);
    }
}
