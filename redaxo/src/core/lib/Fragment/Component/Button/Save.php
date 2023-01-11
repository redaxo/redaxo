<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonType;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use rex_i18n;

class Save
{
    public function __construct(
        public ?string $label = null,
        public ?string $name = null,
        public ?string $value = null,
        public ?string $slot = null,
        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
    }

    public function parse(): string
    {
        $icon = new Icon(
            name: IconLibrary::Save,
            slot: 'prefix'
        );

        $button = new Button(
            label: $this->label ?: rex_i18n::msg('save'),
            prefix: $icon->parse(),
            variant: ButtonVariant::Success,
            type: ButtonType::Submit,
            name: $this->name,
            value: $this->value,
            slot: $this->slot,
            attributes: $this->attributes,
        );

        return $button->parse();
    }
}
