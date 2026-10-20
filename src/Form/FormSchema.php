<?php

namespace Redaxo\Core\Form;

use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Form\Field\Field;
use ReflectionAttribute;
use ReflectionClass;

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
