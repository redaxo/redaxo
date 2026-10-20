<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use BcMath\Number as Decimal;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Input;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Type;
use Redaxo\Core\View\HtmlAttributes;
use Redaxo\Core\View\Renderable;
use ValueError;

use function is_float;
use function is_int;
use function is_string;

/**
 * Number input.
 *
 * The property is an `int`, a `float` for approximate values (measurements, coordinates …) or a `BcMath\Number` for
 * exact decimals (prices …, requires ext-bcmath). `min` and `max` are checked on submit as well, `step` only configures
 * the input element. Pass decimal limits as strings (`step: '0.01'`) to keep them exact.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Number extends Field
{
    public function __construct(
        string|Renderable $label,
        string|Renderable|null $note = null,
        bool $required = false,
        ?string $key = null,
        /** @var int|float|numeric-string|null */
        public readonly int|float|string|null $min = null,
        /** @var int|float|numeric-string|null */
        public readonly int|float|string|null $max = null,
        /**
         * Granularity of the value; without a step the input element allows any decimals for `float` and
         * `BcMath\Number` properties.
         *
         * @var int|float|numeric-string|null
         */
        public readonly int|float|string|null $step = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('int', 'float', Decimal::class);
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
                'min' => null === $this->min ? null : (string) $this->min,
                'max' => null === $this->max ? null : (string) $this->max,
                'step' => null === $this->step ? ('int' === $field->type->name ? null : 'any') : (string) $this->step,
            ]),
        );
    }

    #[Override]
    public function toRaw(mixed $value): string|array
    {
        return $value instanceof Decimal ? (string) $value : parent::toRaw($value);
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): int|float|Decimal|null
    {
        if (!$type->is(Decimal::class)) {
            $value = parent::fromRaw($raw, $type);

            if (null !== $value && !is_int($value) && !is_float($value)) {
                throw new InvalidValueException(new Translatable('form_invalid_value'));
            }

            return null === $value ? null : $this->checkRange($value);
        }

        $raw ??= '';
        if (!is_string($raw)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        if ('' === $raw) {
            return $type->nullable ? null : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        return $this->checkRange(self::decimal($raw));
    }

    #[Override]
    public function encode(mixed $value): string|int|float|bool|array|null
    {
        return $value instanceof Decimal ? (string) $value : parent::encode($value);
    }

    #[Override]
    public function decode(mixed $stored, ValueType $type): int|float|Decimal|null
    {
        if (!$type->is(Decimal::class)) {
            $value = $type->cast($stored);

            return is_int($value) || is_float($value) || null === $value ? $value : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        if (null === $stored || '' === $stored) {
            return $type->nullable ? null : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        return is_string($stored) || is_int($stored) ? self::decimal((string) $stored) : throw new InvalidValueException(new Translatable('form_invalid_value'));
    }

    /**
     * @template T of int|float|Decimal
     * @param T $value
     * @return T
     */
    private function checkRange(int|float|Decimal $value): int|float|Decimal
    {
        if (null !== $this->min && self::compare($value, $this->min) < 0) {
            throw new InvalidValueException(new Translatable('form_min', (string) $this->min));
        }

        if (null !== $this->max && self::compare($value, $this->max) > 0) {
            throw new InvalidValueException(new Translatable('form_max', (string) $this->max));
        }

        return $value;
    }

    private static function compare(int|float|Decimal $value, int|float|string $limit): int
    {
        if ($value instanceof Decimal) {
            return $value->compare(self::decimal((string) $limit));
        }

        return $value <=> (is_string($limit) ? (float) $limit : $limit);
    }

    private static function decimal(string $number): Decimal
    {
        try {
            return new Decimal($number);
        } catch (ValueError) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }
    }
}
