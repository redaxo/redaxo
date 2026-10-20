<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Textarea as TextareaWidget;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Type;

/** Multi-line text input. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Textarea extends Field
{
    public function __construct(
        string|Translatable $label,
        string|Translatable|null $note = null,
        bool $required = false,
        ?string $key = null,
        /** @var positive-int */
        public readonly int $rows = 5,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('string');
    }

    #[Override]
    public function widget(BoundField $field): TextareaWidget
    {
        return new TextareaWidget($field->inputName, Type::string($field->raw), $field->id, $this->rows, $this->required);
    }
}
