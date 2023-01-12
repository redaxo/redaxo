<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Fragment\Slot;
use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use rex_i18n;

class Add
{
    public function __construct(
        public ?string $href = null,
        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
    }

    public function parse(): string
    {
        $icon = new Icon(
            name: IconLibrary::Add,
        );

        $button = new Button(
            slotDefault: new Slot(rex_i18n::msg('add')),
            slotPrefix: new Slot($icon->parse()),
            href: $this->href,
            variant: ButtonVariant::Primary,
            attributes: $this->attributes,
        );

        return $button->parse();
    }
}
