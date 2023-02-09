<?php

namespace Redaxo\Core\Fragment\Component\Alert;

use Redaxo\Core\Fragment\Component\Alert;
use Redaxo\Core\Fragment\Component\AlertType;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Slot;

class Error
{
    public function __construct(
        /** @see Alert::$slotDefault */
        public Slot $slotDefault,

        /** @see Alert::$duration */
        public ?int $duration = null,

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {}

    public function render(): string
    {
        $icon = new Icon(
            name: IconLibrary::AlertError,
        );

        $alert = new Alert(
            slotDefault: $this->slotDefault,
            slotIcon: new Slot($icon->render()),
            open: true,
            type: AlertType::Error,
            attributes: $this->attributes,
        );

        return $alert->render();
    }
}
