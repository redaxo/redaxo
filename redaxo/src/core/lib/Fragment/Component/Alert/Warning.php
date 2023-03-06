<?php

namespace Redaxo\Core\Fragment\Component\Alert;

use Redaxo\Core\Fragment\Component\Alert;
use Redaxo\Core\Fragment\Component\AlertType;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Fragment;

class Warning extends Fragment
{
    public function __construct(
        /** @see Alert::$body */
        public string|Fragment $body,

        /** @see Alert::$duration */
        public ?int $duration = null,

        /** @var array<string, string|int> */
        public array $attributes = [],
    ) {}

    public function render(): string
    {
        $alert = new Alert(
            body: $this->body,
            icon: new Icon(IconLibrary::AlertWarning),
            open: true,
            type: AlertType::Warning,
            attributes: $this->attributes,
        );

        return $alert->render();
    }
}