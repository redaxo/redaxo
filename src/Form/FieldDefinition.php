<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Form\Field\Field;
use ReflectionProperty;

/**
 * A property of a form data class together with its field type.
 *
 * @internal
 */
final readonly class FieldDefinition
{
    public string $name;

    /** Key used by storages. */
    public string $key;

    public function __construct(
        private ReflectionProperty $property,
        public Field $field,
        public ValueType $type,
        /** The group the field belongs to, i.e. the last fieldset started at or before it. */
        public ?Fieldset $fieldset = null,
    ) {
        $this->name = $property->name;
        $this->key = $field->key ?? $property->name;
    }

    /** @param class-string $class */
    public function hasAttribute(string $class): bool
    {
        return [] !== $this->property->getAttributes($class);
    }

    public function isInitialized(object $data): bool
    {
        return $this->property->isInitialized($data);
    }

    public function getValue(object $data): mixed
    {
        return $this->property->isInitialized($data) ? $this->property->getValue($data) : null;
    }

    public function setValue(object $data, mixed $value): void
    {
        $this->property->setValue($data, $value);
    }
}
