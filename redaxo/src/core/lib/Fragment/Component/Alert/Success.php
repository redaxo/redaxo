<?php

namespace Redaxo\Core\Fragment\Component\Alert;

use Redaxo\Core\Fragment\Component\Alert;
use Redaxo\Core\Fragment\Component\AlertType;
use Redaxo\Core\Fragment\Component\Icon;
use Redaxo\Core\Fragment\Component\IconLibrary;
use Redaxo\Core\Fragment\Fragment;
use Redaxo\Core\Fragment\HtmlAttributes;

final class Success extends Fragment
{
    public function __construct(
        /** @see Alert::$body */
        public string|Fragment $body,

        /** @see Alert::$duration */
        public ?int $duration = null,

        public HtmlAttributes $attributes = new HtmlAttributes(),
    ) {}

    public function render(): string
    {
        $alert = new Alert(
            body: $this->body,
            icon: new Icon(IconLibrary::AlertSuccess),
            open: true,
            type: AlertType::Success,
            attributes: $this->attributes,
        );

        return $alert->render();
    }
}
