<?php

namespace Redaxo\Core\Fragment\Component\Button;

use Redaxo\Core\Fragment\Attributes;
use Redaxo\Core\Fragment\Component\Button;
use Redaxo\Core\Fragment\Component\ButtonVariant;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Fragment;
use rex_i18n;

class Add extends Fragment
{
    public function __construct(
        public ?string $href = null,
        public Attributes $attributes = new Attributes(),
    ) {}

    public function render(): string
    {
        $button = new Button(
            label: rex_i18n::rawMsg('add'),
            prefix: new Icon(IconLibrary::Add),
            href: $this->href,
            variant: ButtonVariant::Primary,
            attributes: $this->attributes,
        );

        return $button->render();
    }
}
