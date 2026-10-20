<?php

namespace Redaxo\Core\Form\Field;

use BackedEnum;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\FieldRow;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Widget;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\View\Renderable;

use function array_keys;
use function explode;
use function is_array;
use function is_bool;
use function is_scalar;
use function is_string;

/**
 * Base class of all field types.
 *
 * A field type is an attribute on a typed property of a form data class: the property holds the value in its real
 * PHP type, the attribute decides how it is edited. Besides rendering, a field converts between three
 * representations of its value:
 *
 * - the property value (e.g. an `Article`),
 * - the raw value the widget displays and the request carries (always strings),
 * - the stored value a storage persists (scalars, e.g. the article id).
 */
abstract class Field
{
    public function __construct(
        public readonly string|Renderable $label,
        public readonly string|Renderable|null $note = null,
        public readonly bool $required = false,
        /** Key used by storages; defaults to the property name. */
        public readonly ?string $key = null,
    ) {}

    /** Whether the field can edit a property of the given type; checked when reading the form schema. */
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('string', 'int', 'float', 'bool', BackedEnum::class);
    }

    /** Creates the widget editing the field, initialized with its current raw value. */
    abstract public function widget(BoundField $field): Widget;

    /** Creates the complete form group: label, widget, note and error. */
    public function row(BoundField $field): Renderable
    {
        return new FieldRow($this->widget($field), $this->label, note: $this->note, error: $field->error, required: $this->required);
    }

    /** @return string|list<string> */
    public function toRaw(mixed $value): string|array
    {
        return self::scalarToRaw($value);
    }

    /** @throws InvalidValueException */
    protected static function scalarToRaw(mixed $value): string
    {
        return match (true) {
            null === $value => '',
            $value instanceof BackedEnum => (string) $value->value,
            is_bool($value) => $value ? '1' : '0',
            is_scalar($value) => (string) $value,
            default => throw new InvalidValueException(new Translatable('form_invalid_value')),
        };
    }

    /**
     * Converts the submitted raw value into the property value.
     *
     * @param string|list<string>|null $raw `null` if the request did not contain the field at all
     * @throws InvalidValueException
     * @return string|int|float|bool|array<array-key, mixed>|object|null
     */
    public function fromRaw(string|array|null $raw, ValueType $type): string|int|float|bool|array|object|null
    {
        $raw ??= '';
        if (!is_string($raw)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        return '' === $raw && $type->nullable ? null : $type->cast($raw);
    }

    /** Whether the value counts as missing for {@see self::$required}. */
    public function isEmpty(mixed $value): bool
    {
        return null === $value || '' === $value || [] === $value;
    }

    /**
     * Converts the property value into the representation a storage persists.
     *
     * @return string|int|float|bool|array<array-key, mixed>|null
     */
    public function encode(mixed $value): string|int|float|bool|array|null
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            null === $value, is_scalar($value), is_array($value) => $value,
            default => throw new InvalidValueException(new Translatable('form_invalid_value')),
        };
    }

    /**
     * Converts a stored value back into the property value.
     *
     * @throws InvalidValueException
     * @return string|int|float|bool|array<array-key, mixed>|object|null
     */
    public function decode(mixed $stored, ValueType $type): string|int|float|bool|array|object|null
    {
        return $type->cast($stored);
    }

    /**
     * Splits a comma-separated raw value (or takes a list as is), dropping empty items.
     *
     * @param string|array<array-key, mixed>|null $raw
     * @return list<string>
     */
    protected static function splitList(string|array|null $raw): array
    {
        $items = is_array($raw) ? $raw : explode(',', $raw ?? '');

        $list = [];
        foreach (array_keys($items) as $key) {
            if (is_string($items[$key]) && '' !== $items[$key]) {
                $list[] = $items[$key];
            }
        }

        return $list;
    }
}
