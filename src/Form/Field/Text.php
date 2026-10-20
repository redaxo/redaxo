<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Input;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Type;

/** Single-line text input. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Text extends Field
{
    public function __construct(
        string|Translatable $label,
        string|Translatable|null $note = null,
        bool $required = false,
        ?string $key = null,
        /** @var int<1, 65535>|null */
        public readonly ?int $maxLength = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('string', 'int', 'float');
    }

    #[Override]
    public function widget(BoundField $field): Input
    {
        return new Input($field->inputName, Type::string($field->raw), $field->id, maxLength: $this->maxLength, required: $this->required);
    }
}
