<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonType;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;
use rex_i18n;

final class Save extends Fragment
{
    public function __construct(
        public string|Fragment|null $label = null,
        public ?string $name = null,
        public ?string $value = null,
        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    public function render(): string
    {
        $button = new Button(
            label: $this->label ?? rex_i18n::rawMsg('form_save'),
            prefix: new Icon(IconLibrary::Save),
            variant: ButtonVariant::Success,
            type: ButtonType::Submit,
            name: $this->name,
            value: $this->value,
            attributes: $this->attributes,
        );

        return $button->render();
    }
}
