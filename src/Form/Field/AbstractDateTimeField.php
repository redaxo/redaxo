<?php

namespace Redaxo\Core\Form\Field;

use DateTimeImmutable;
use DateTimeInterface;
use Override;
use Redaxo\Core\Form\BoundField;
use Redaxo\Core\Form\InvalidValueException;
use Redaxo\Core\Form\ValueType;
use Redaxo\Core\Form\Widget\Input;
use Redaxo\Core\Translation\Translatable;
use Redaxo\Core\Util\Type;

use function is_string;

/**
 * Base of the HTML5 date and time inputs.
 *
 * The property is either a `DateTimeImmutable` (or `DateTimeInterface`) or a `string` in the storage format (e.g.
 * `Y-m-d` for a date), which is also what storages persist.
 */
abstract class AbstractDateTimeField extends Field
{
    /** The `type` of the input element. */
    abstract protected function inputType(): string;

    /** Format of the value the input element displays. */
    abstract protected function inputFormat(): string;

    /**
     * Formats the input element may submit, e.g. with or without seconds.
     *
     * @return non-empty-list<string>
     */
    abstract protected function parseFormats(): array;

    /** Format of string properties and of the stored value, matching the SQL column type. */
    abstract protected function storageFormat(): string;

    #[Override]
    public function supports(ValueType $type): bool
    {
        return $type->isAnyOf('string', DateTimeImmutable::class) || DateTimeInterface::class === $type->name;
    }

    #[Override]
    public function widget(BoundField $field): Input
    {
        return new Input($field->inputName, Type::string($field->raw), $field->id, $this->inputType(), required: $this->required);
    }

    #[Override]
    public function toRaw(mixed $value): string
    {
        $date = $this->toDateTime($value);

        return null === $date ? '' : $date->format($this->inputFormat());
    }

    #[Override]
    public function fromRaw(string|array|null $raw, ValueType $type): DateTimeImmutable|string|null
    {
        $raw ??= '';
        if (!is_string($raw)) {
            throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        if ('' === $raw) {
            return $type->nullable ? null : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        foreach ($this->parseFormats() as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $raw);

            if (false !== $date && $date->format($format) === $raw) {
                return $this->fromDateTime($date, $type);
            }
        }

        throw new InvalidValueException(new Translatable('form_invalid_value'));
    }

    #[Override]
    public function encode(mixed $value): ?string
    {
        return $this->toDateTime($value)?->format($this->storageFormat());
    }

    #[Override]
    public function decode(mixed $stored, ValueType $type): DateTimeImmutable|string|null
    {
        if (null === $stored || '' === $stored) {
            return $type->nullable ? null : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $date = $this->toDateTime($stored);

        return null === $date ? throw new InvalidValueException(new Translatable('form_invalid_value')) : $this->fromDateTime($date, $type);
    }

    private function toDateTime(mixed $value): ?DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }

        if (!is_string($value) || '' === $value) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('!' . $this->storageFormat(), $value) ?: null;
    }

    private function fromDateTime(DateTimeImmutable $date, ValueType $type): DateTimeImmutable|string
    {
        return 'string' === $type->name ? $date->format($this->storageFormat()) : $date;
    }
}
