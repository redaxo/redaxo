<?php

namespace Redaxo\Core\Form;

use BackedEnum;
use Redaxo\Core\Exception\LogicException;
use Redaxo\Core\Translation\Translatable;
use ReflectionNamedType;
use ReflectionProperty;

use function class_exists;
use function filter_var;
use function interface_exists;
use function is_a;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function sprintf;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOL;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

/**
 * The declared type of a form property, used to cast submitted and stored scalars into it.
 *
 * Only single named types are supported (optionally nullable), no unions or intersections.
 */
final readonly class ValueType
{
    private function __construct(
        /** Builtin type name (`string`, `int`, `float`, `bool`, `array`) or class name. */
        public string $name,
        public bool $nullable,
    ) {}

    /** @internal */
    public static function of(ReflectionProperty $property): self
    {
        $type = $property->getType();

        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException(sprintf('Form property %s::$%s must declare a single named type.', $property->class, $property->name));
        }

        return new self($type->getName(), $type->allowsNull());
    }

    /** @param class-string $class */
    public function is(string $class): bool
    {
        return is_a($this->name, $class, true);
    }

    /**
     * Whether the type is one of the given builtin types or a subtype of one of the given classes.
     *
     * @param string ...$types builtin type names or class names
     */
    public function isAnyOf(string ...$types): bool
    {
        foreach ($types as $type) {
            if ($type === $this->name || ((class_exists($type) || interface_exists($type)) && is_a($this->name, $type, true))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Casts a scalar into this type.
     *
     * @throws InvalidValueException if the value can not be represented by this type
     */
    public function cast(mixed $value): string|int|float|bool|BackedEnum|null
    {
        if (null === $value) {
            return $this->nullable ? null : throw new InvalidValueException(new Translatable('form_invalid_value'));
        }

        $class = $this->name;
        $result = match (true) {
            'string' === $class => is_string($value) || is_int($value) || is_float($value) ? (string) $value : null,
            'int' === $class => is_int($value) ? $value : filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            'float' === $class => is_float($value) || is_int($value) ? (float) $value : filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
            'bool' === $class => is_bool($value) ? $value : filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            is_a($class, BackedEnum::class, true) && (is_string($value) || is_int($value)) => $class::tryFrom($value),
            default => null,
        };

        return $result ?? throw new InvalidValueException(new Translatable('form_invalid_value'));
    }
}
