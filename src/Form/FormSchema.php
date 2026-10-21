<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Field\Field;
use Redaxo\Core\Form\Field\ReferencesItems;
use ReflectionAttribute;
use ReflectionClass;

use function array_key_exists;
use function array_values;
use function count;
use function sprintf;

/**
 * The fields of a form data class, read from the field attributes on its properties.
 *
 * @internal
 */
final class FormSchema
{
    /** @var array<class-string, self> */
    private static array $cache = [];

    /** @param array<string, FieldDefinition> $fields keyed by property name, in declaration order */
    private function __construct(
        public readonly array $fields,
    ) {}

    /** @param class-string $class */
    public static function of(string $class): self
    {
        return self::$cache[$class] ??= self::read($class);
    }

    public function get(string $name): FieldDefinition
    {
        return $this->fields[$name] ?? throw new LogicException(sprintf('Unknown form field "%s".', $name));
    }

    /**
     * Writes stored values into the data object; values that are missing or no longer fit the field keep the default.
     *
     * @param array<array-key, mixed> $stored keyed by storage key
     */
    public function hydrate(object $data, array $stored): void
    {
        foreach ($this->fields as $definition) {
            if (!array_key_exists($definition->key, $stored)) {
                continue;
            }

            try {
                $value = $definition->field->decode($stored[$definition->key], $definition->type);
            } catch (InvalidValueException) {
                continue;
            }

            $definition->setValue($data, $value);
        }
    }

    /**
     * Returns the values of the data object in the representation a storage persists.
     *
     * @return array<string, string|int|float|bool|array<array-key, mixed>|null> keyed by storage key
     */
    public function encode(object $data): array
    {
        $stored = [];
        foreach ($this->fields as $definition) {
            $stored[$definition->key] = $definition->field->encode($definition->getValue($data));
        }

        return $stored;
    }

    /**
     * Returns the items the field values of the data object refer to, see {@see ReferencesItems}.
     *
     * @return list<Reference>
     */
    public function references(object $data): array
    {
        $references = [];
        foreach ($this->fields as $definition) {
            if ($definition->field instanceof ReferencesItems) {
                foreach ($definition->field->references($definition->getValue($data)) as $reference) {
                    $references[$reference->type . ':' . $reference->target] = $reference;
                }
            }
        }

        return array_values($references);
    }

    /** @param class-string $class */
    private static function read(string $class): self
    {
        $fields = [];
        $fieldset = null;

        foreach (new ReflectionClass($class)->getProperties() as $property) {
            $attributes = $property->getAttributes(Field::class, ReflectionAttribute::IS_INSTANCEOF);

            if (!$attributes) {
                continue;
            }

            if (count($attributes) > 1) {
                throw new LogicException(sprintf('Form property %s::$%s has more than one field attribute.', $class, $property->name));
            }

            if ($property->isStatic() || !$property->isPublic()) {
                throw new LogicException(sprintf('Form property %s::$%s must be public and non-static.', $class, $property->name));
            }

            // A field without value (a new item, a field added later) keeps the default, so it must have one.
            if (!$property->hasDefaultValue() && !$property->isPromoted()) {
                throw new LogicException(sprintf('Form property %s::$%s must have a default value.', $class, $property->name));
            }

            $field = $attributes[0]->newInstance();
            $type = ValueType::of($property);

            if (!$field->supports($type)) {
                throw new LogicException(sprintf('Form property %s::$%s of type %s is not supported by field type %s.', $class, $property->name, $type->name, $field::class));
            }

            $fieldset = ($property->getAttributes(Fieldset::class)[0] ?? null)?->newInstance() ?? $fieldset;

            $fields[$property->name] = new FieldDefinition($property, $field, $type, $fieldset);
        }

        return new self($fields);
    }
}
