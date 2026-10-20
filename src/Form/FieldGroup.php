<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\View\Html;
use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;

/** A `<fieldset>` around the rows of a group of fields. */
class FieldGroup implements Renderable
{
    use RendersView;

    public function __construct(
        public Html $content,
        public string|Renderable|null $legend = null,
    ) {}
}
