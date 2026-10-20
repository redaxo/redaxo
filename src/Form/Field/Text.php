<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Input;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Type;
use Redaxo\Core\View\Renderable;

use function mb_strlen;

/** Single-line text input. */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Text extends Field
{
    public function __construct(
        string|Renderable $label,
        string|Renderable|null $note = null,
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
        return $type->isAnyOf('string');
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): ?string
    {
        $value = parent::fromRaw($raw, $type);
        $value = null === $value ? null : Type::string($value);

        if (null !== $value && null !== $this->maxLength && mb_strlen($value) > $this->maxLength) {
            throw new InvalidValueException(new Translatable('form_max_length', $this->maxLength));
        }

        return $value;
    }

    #[Override]
    public function widget(BoundField $field): Input
    {
        return new Input($field->inputName, Type::string($field->raw), $field->id, maxLength: $this->maxLength, required: $this->required);
    }
}
