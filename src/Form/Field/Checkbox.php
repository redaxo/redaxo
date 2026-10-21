<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\CheckboxInput;

/**
 * Single checkbox for a `bool` property; `required` means it has to be checked.
 *
 * The label is rendered next to the checkbox by the widget, not in front of it by the row.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Checkbox extends Field
{
    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('bool');
    }

    #[Override]
    public function widget(BoundField $field): CheckboxInput
    {
        return new CheckboxInput($field->inputName, $this->label, '1' === $field->raw, id: $field->id, required: $this->required, disabled: $field->disabled);
    }

    #[Override]
    public function row(BoundField $field): FieldRow
    {
        return new FieldRow($this->widget($field), note: $this->note, error: $field->error, required: $this->required);
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): bool
    {
        return null !== $raw && '' !== $raw && '0' !== $raw;
    }

    #[Override]
    public function isEmpty(mixed $value): bool
    {
        return !$value;
    }
}
