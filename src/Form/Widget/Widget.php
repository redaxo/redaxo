<?php

namespace Redaxo\Core\Form\Widget;

use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\View\Renderable;

/** A form control that can be rendered standalone or within a {@see FieldRow}. */
interface Widget extends Renderable
{
    /** Id of the element a label in front of the widget points to, `null` if there is none. */
    public function labelTarget(): ?string;
}
