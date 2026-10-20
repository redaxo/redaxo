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
use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\Renderable;

use function is_float;
use function is_int;

/**
 * Number input for an `int` or `float` property.
 *
 * `min` and `max` are checked on submit as well; `step` only configures the input element.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Number extends Field
{
    public function __construct(
        string|Renderable $label,
        string|Renderable|null $note = null,
        bool $required = false,
        ?string $key = null,
        public readonly int|float|null $min = null,
        public readonly int|float|null $max = null,
        /** Granularity of the value; for `float` properties without a step the input element allows any decimals. */
        public readonly int|float|null $step = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('int', 'float');
    }

    #[Override]
    public function widget(BoundField $field): Input
    {
        return new Input(
            $field->inputName,
            Type::string($field->raw),
            $field->id,
            'number',
            required: $this->required,
            attributes: new HtmlAttributes([
                'min' => self::attribute($this->min),
                'max' => self::attribute($this->max),
                'step' => self::attribute($this->step) ?? ('float' === $field->type->name ? 'any' : null),
            ]),
        );
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): int|float|null
    {
        $value = parent::fromRaw($raw, $type);

        if (null === $value) {
            return null;
        }

        if (!is_int($value) && !is_float($value)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        if (null !== $this->min && $value < $this->min) {
            throw new InvalidValueException(new Translatable('form_min', (string) $this->min));
        }

        if (null !== $this->max && $value > $this->max) {
            throw new InvalidValueException(new Translatable('form_max', (string) $this->max));
        }

        return $value;
    }

    private static function attribute(int|float|null $number): ?string
    {
        return null === $number ? null : (string) $number;
    }
}
