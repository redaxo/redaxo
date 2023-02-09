<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Slot;
use rex_i18n;

class Add
{
    public function __construct(
        public ?string $href = null,
        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {}

    public function render(): string
    {
        $icon = new Icon(
            name: IconLibrary::Add,
        );

        $button = new Button(
            slotDefault: new Slot(rex_i18n::msg('add')),
            slotPrefix: new Slot($icon->render()),
            href: $this->href,
            variant: ButtonVariant::Primary,
            attributes: $this->attributes,
        );

        return $button->render();
    }
}
