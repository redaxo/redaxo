<?php

namespace Redaxo\Core\Form\Field;

use Attribute;
use BackedEnum;
use Closure;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\ChoiceList;
use Redaxo\Core\Form\Widget\SelectInput;
use Redaxo\Core\Translation\Translatable;

use function array_key_exists;
use function array_key_first;
use function array_map;
use function array_values;
use function is_array;
use function is_scalar;
use function is_string;

/**
 * One or (with `multiple`) several of a set of choices, rendered as a select box or (with `expanded`) as radio
 * buttons/checkboxes:
 *
 * | expanded | multiple | widget          |
 * |----------|----------|-----------------|
 * | false    | false    | select          |
 * | false    | true     | multi-select    |
 * | true     | false    | radio buttons   |
 * | true     | true     | checkboxes      |
 *
 * The choices are given as `value => label`, either directly or by a callable returning them — typically a
 * first-class callable to a static method (`choices: self::choices(...)`), as attribute arguments have to be
 * constant expressions. A nested array groups choices, its key being the group label.
 *
 * A single value is cast into the property type, so `int` and backed enum properties work as well. With `multiple`
 * the property is an `array` holding the selected choice keys.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Choice extends Field
{
    /**
     * @param array<int|string, string|Translatable|array<int|string, string|Translatable>>|Closure(): iterable<int|string, string|Translatable|array<int|string, string|Translatable>> $choices
     * @param string|Translatable|null $placeholder label of an empty option of a single select; nullable properties get
     *     an empty one by default
     */
    public function __construct(
        string|Translatable $label,
        private readonly array|Closure $choices,
        public readonly bool $multiple = false,
        public readonly bool $expanded = false,
        public readonly string|Translatable|null $placeholder = null,
        string|Translatable|null $note = null,
        bool $required = false,
        ?string $key = null,
    ) {
        parent::__construct($label, $note, $required, $key);
    }

    /** @return array<int|string, string|Translatable|array<int|string, string|Translatable>> */
    public function choices(): array
    {
        if (!$this->choices instanceof Closure) {
            return $this->choices;
        }

        $choices = [];
        foreach (($this->choices)() as $value => $label) {
            $choices[$value] = $label;
        }

        return $choices;
    }

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $this->multiple ? $type->isAnyOf('array') : parent::supports($type);
    }

    #[Override]
    public function widget(BoundField $field): SelectInput|ChoiceList
    {
        $selected = $this->multiple ? self::splitList($field->raw) : (is_string($field->raw) ? $field->raw : null);

        if ($this->expanded) {
            return new ChoiceList($field->inputName, $this->choices(), $selected, $field->id, $this->multiple);
        }

        return new SelectInput(
            $field->inputName,
            $this->choices(),
            $selected,
            $field->id,
            $this->multiple,
            $this->placeholder ?? ($field->type->nullable ? '' : null),
            $this->required,
        );
    }

    #[Override]
    public function toRaw(mixed $value): string|array
    {
        if (!$this->multiple) {
            return parent::toRaw($value);
        }

        if (!is_array($value)) {
            return [];
        }

        $raw = [];
        foreach (array_keys($value) as $key) {
            $raw[] = self::scalarToRaw($value[$key]);
        }

        return $raw;
    }

    /** @return string|int|float|bool|BackedEnum|list<int|string>|null */
    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): string|int|float|bool|BackedEnum|array|null
    {
        $choices = $this->flatChoices();

        if ($this->multiple) {
            $values = [];
            foreach (self::splitList($raw) as $item) {
                if (!array_key_exists($item, $choices)) {
                    throw new InvalidValueException(new Translatable('form_invalid_choice'));
                }
                // restores the int type of numeric keys, as PHP does for the choices array itself
                $values[] = array_key_first([$item => true]);
            }

            return $values;
        }

        $raw ??= '';
        if (!is_string($raw)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        if ('' === $raw && $type->nullable) {
            return null;
        }

        if (!array_key_exists($raw, $choices)) {
            throw new InvalidValueException(new Translatable('form_invalid_choice'));
        }

        return $type->cast($raw);
    }

    /** @return string|int|float|bool|BackedEnum|list<int|string>|null */
    #[Override]
    public function decode(mixed $stored, ValueType $type): string|int|float|bool|BackedEnum|array|null
    {
        if (!$this->multiple) {
            return $type->cast($stored);
        }

        if (!is_array($stored)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        return $this->fromRaw(array_map(static fn (mixed $item): string => is_scalar($item) ? (string) $item : '', array_values($stored)), $type);
    }

    /** @return array<int|string, string|Translatable> */
    private function flatChoices(): array
    {
        $flat = [];
        foreach ($this->choices() as $value => $label) {
            if (is_array($label)) {
                $flat += $label;
            } else {
                $flat[$value] = $label;
            }
        }

        return $flat;
    }
}
