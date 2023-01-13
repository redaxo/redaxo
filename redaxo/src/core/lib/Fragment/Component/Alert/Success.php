<?php

namespace Redaxo\Core\Fragment\Component\Alert;

use Redaxo\Core\Fragment\Component\Alert;
use Redaxo\Core\Fragment\Component\AlertType;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Slot;
use rex_i18n;

class Success
{
    public function __construct(
        /** @see Alert::$slotDefault */
        public Slot $slotDefault,

        /** @see Alert::$duration */
        public ?int $duration = null,

        /** @var array<string, string>|null */
        public ?array $attributes = null,
    ) {
    }

    public function parse(): string
    {
        $icon = new Icon(
            name: IconLibrary::AlertSuccess,
        );

        $alert = new Alert(
            slotDefault: $this->slotDefault,
            slotIcon: new Slot($icon->parse()),
            open: true,
            type: AlertType::Success,
            attributes: $this->attributes,
        );

        return $alert->parse();
    }
}
