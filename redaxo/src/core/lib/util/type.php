<?php

/**
 * Class for var casting.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_type
{
    /**
     * Casts the variable $var to $vartype.
     *
     * Possible types:
     *  - 'bool' (or 'boolean')
     *  - 'int' (or 'integer')
     *  - 'double'
     *  - 'string'
     *  - 'float'
     *  - 'real'
     *  - 'object'
     *  - 'array'
     *  - 'array[<type>]', e.g. 'array[int]'
     *  - '' (don't cast)
     *  - a callable
     *  - array(
     *      array(<key>, <vartype>, <default>),
     *      array(<key>, <vartype>, <default>),
     *      ...
     *    )
     *
     * @param mixed $var Variable to cast
     * @param string|callable(mixed):mixed|list<array{string, string, mixed}> $vartype Variable type
     *
     * @throws InvalidArgumentException
     *
     * @return mixed Casted value
     *
     * @psalm-taint-specialize
     */
    public static function cast($var, $vartype)
    {
        if (is_string($vartype)) {
            $casted = true;
            switch ($vartype) {
                // ---------------- PHP types
                case 'bool':
                case 'boolean':
                    $var = (bool) $var;
                    break;
                case 'int':
                case 'integer':
                    $var = (int) $var;
                    break;
                case 'double':
                case 'float':
                case 'real':
                    $var = (float) $var;
                    break;
                case 'string':
                    if (is_array($var)) { // https://github.com/redaxo/redaxo/issues/2900
                        $var = '';
                    } else {
                        $var = (string) $var;
                    }
                    break;
                case 'object':
                    $var = (object) $var;
                    break;
                case 'array':
                    if ('' === $var) {
                        $var = [];
                    } else {
                        $var = (array) $var;
                    }
                    break;

                    // kein Cast, nichts tun
                case '': break;
                default:
                    // check for array with generic type
                    if (str_starts_with($vartype, 'array[')) {
                        if (empty($var)) {
                            $var = [];
                        } else {
                            $var = (array) $var;
                        }

                        // check if every element in the array is from the generic type
                        $matches = [];
                        if (preg_match('@array\[([^\]]*)\]@', $vartype, $matches)) {
                            foreach ($var as $key => $value) {
                                try {
                                    $var[$key] = self::cast($value, $matches[1]);
                                } catch (InvalidArgumentException) {
                                    // Evtl Typo im vartype, mit urspr. typ als fehler melden
                                    throw new InvalidArgumentException('Unexpected vartype "' . $vartype . '" in cast()!');
                                }
                            }
                        } else {
                            throw new InvalidArgumentException('Unexpected vartype "' . $vartype . '" in cast()!');
                        }
                    } else {
                        $casted = false;
                    }
            }
            if ($casted) {
                return $var;
            }
        }

        if (is_callable($vartype)) {
            $var = call_user_func($vartype, $var);
        } elseif (is_array($vartype)) {
            $var = self::cast($var, 'array');
            $newVar = [];
            foreach ($vartype as $cast) {
                if (!is_array($cast) || !isset($cast[0])) {
                    throw new InvalidArgumentException('Unexpected vartype in cast()!');
                }
                $key = $cast[0];
                $innerVartype = $cast[1] ?? '';
                if (array_key_exists($key, $var)) {
                    $newVar[$key] = self::cast($var[$key], $innerVartype);
                } elseif (!isset($cast[2])) {
                    $newVar[$key] = self::cast('', $innerVartype);
                } else {
                    $newVar[$key] = $cast[2];
                }
            }
            $var = $newVar;
        } elseif (is_string($vartype)) {
            throw new InvalidArgumentException('Unexpected vartype "' . $vartype . '" in cast()!');
        } else {
            throw new InvalidArgumentException('Unexpected vartype in cast()!');
        }

        return $var;
    }

    /**
     * @template T
     * @param T|null $value
     * @return T
     * @psalm-assert !null $value
     * @psalm-pure
     */
    public static function notNull(mixed $value): mixed
    {
        if (null === $value) {
            throw new InvalidArgumentException('Exptected a value other than null');
        }

        return $value;
    }

    /**
     * @psalm-assert bool $value
     * @psalm-pure
     */
    public static function bool(mixed $value): bool
    {
        if (!is_bool($value)) {
            throw new InvalidArgumentException('Exptected a boolean, but got '.get_debug_type($value));
        }

        return $value;
    }

    /**
     * @psalm-assert ?bool $value
     * @psalm-pure
     */
    public static function nullOrBool(mixed $value): ?bool
    {
        return null === $value ? null : self::bool($value);
    }

    /**
     * @param mixed $value
     * @psalm-assert string $value
     * @psalm-pure
     */
    public static function string($value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Exptected a string, but got '.get_debug_type($value));
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @psalm-assert ?string $value
     * @psalm-pure
     */
    public static function nullOrString($value): ?string
    {
        return null === $value ? null : self::string($value);
    }

    /**
     * @param mixed $value
     * @psalm-assert int $value
     * @psalm-pure
     */
    public static function int($value): int
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException('Exptected an integer, but got '.get_debug_type($value));
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @psalm-assert ?int $value
     * @psalm-pure
     */
    public static function nullOrInt($value): ?int
    {
        return null === $value ? null : self::int($value);
    }

    /**
     * @param mixed $value
     * @psalm-assert array $value
     * @psalm-pure
     */
    public static function array($value): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Exptected an array, but got '.get_debug_type($value));
        }

        return $value;
    }

    /**
     * @template T of object
     * @param mixed $value
     * @param class-string<T> $class
     * @return T
     * @psalm-assert T $value
     * @psalm-pure
     */
    public static function instanceOf($value, string $class): object
    {
        if (!$value instanceof $class) {
            throw new InvalidArgumentException('Exptected a '.$class.', but got '.get_debug_type($value));
        }

        return $value;
    }
}
