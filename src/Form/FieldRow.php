<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Form\Widget\Widget;
use Redaxo\Core\View\Renderable;
use Redaxo\Core\View\RendersView;

/** The form group around a widget: label, widget, note and error. */
final class FieldRow implements Renderable
{
    use RendersView;

    public function __construct(
        public Renderable $widget,
        /** `null` for widgets that render their label themselves (e.g. a checkbox). */
        public string|Renderable|null $label = null,
        /** Id of the element the label points to; defaults to the {@see Widget::labelTarget()} of the widget. */
        public ?string $labelFor = null,
        public string|Renderable|null $note = null,
        public string|Renderable|null $error = null,
        public bool $required = false,
        /** Whether the label is placed above the widget instead of next to it, e.g. in narrow columns. */
        public bool $vertical = false,
    ) {}

    public function labelFor(): ?string
    {
        return $this->labelFor ?? ($this->widget instanceof Widget ? $this->widget->labelTarget() : null);
    }
}
