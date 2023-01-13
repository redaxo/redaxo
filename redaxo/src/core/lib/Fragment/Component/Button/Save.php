<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonType;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Slot;
use rex_i18n;

class Save
{
    public function __construct(
        public ?Slot $slotDefault = null,
        public ?string $name = null,
        public ?string $value = null,
        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
    }

    public function parse(): string
    {
        $icon = new Icon(
            name: IconLibrary::Save,
        );

        $button = new Button(
            slotDefault: $this->slotDefault ?: new Slot(rex_i18n::msg('save')),
            slotPrefix: new Slot($icon->parse()),
            variant: ButtonVariant::Success,
            type: ButtonType::Submit,
            name: $this->name,
            value: $this->value,
            attributes: $this->attributes,
        );

        return $button->parse();
    }
}
