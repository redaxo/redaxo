<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use rex_i18n;

class Add
{
    public function __construct(
        public ?string $href = null,
        public ?string $slot = null,
        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
    }

    public function parse(): string
    {
        $icon = new Icon(
            name: IconLibrary::Add,
            slot: 'prefix'
        );

        $button = new Button(
            label: rex_i18n::msg('add'),
            href: $this->href,
            prefix: $icon->parse(),
            variant: ButtonVariant::Primary,
            slot: $this->slot,
            attributes: $this->attributes,
        );

        return $button->parse();
    }
}
