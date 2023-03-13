<?php

namespace Redaxo\Core\Fragment;

use BackedEnum;
use rex_type;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;

/**
 * Container for HTML attributes.
 *
 * ```php
 * $attributes = new Attributes([
 *     'attr1' => 'my_value', // string value
 *     'attr2' => 5, // integer value
 *     'attr3' => $myEnum, // BackedEnum values
 *     'attr4' => null', // attributes with `null`/`false` value are omitted
 *     'disabled' => true, // `true` value for boolean attributes without value
 *     'class' => [ // arrays for space separated attributes
 *         'cls1',
 *         'cls2',
 *         'cls3' => false, // conditional classes
 *         'cls4' => true,  // conditional classes
 *     ],
 * ]);
 * $attributes->toString();
 * ```
 *
 * The example will result in this attributes string:
 *    ` attr1="my_value" attr2="5" attr3="my_enum_value" disabled class="cls1 cls2 cls4"`
 */
final class HtmlAttributes
{
    public function __construct(
        /** @var array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>> */
        private array $attributes = [],
    ) {}

    /**
     * @param literal-string $key
     * @param null|bool|string|int|BackedEnum|array<string|int, string|bool> $value
     */
    public function set(string $key, null|bool|string|int|BackedEnum|array $value): void
    {
        $this->attributes[$key] = $value;
    }

    /** @param array<literal-string, null|bool|string|int|BackedEnum|array<string|int, string|bool>> $attributes */
    public function with(array $attributes): self
    {
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $attributes)) {
                $attributes[$key] = $value;
                continue;
            }

            if (!is_array($attributes[$key])) {
                continue;
            }

            if (is_array($value)) {
                $value = self::normalizeArrayValue($value);
            } elseif (is_string($value)) {
                $value = self::normalizeArrayValue(explode(' ', $value));
            } else {
                continue;
            }

            $normalized = self::normalizeArrayValue($attributes[$key]);

            foreach ($value as $part => $enabled) {
                if (!$enabled) {
                    continue;
                }
                if (isset($normalized[$part])) {
                    continue;
                }

                $attributes[$key][] = $part;
            }
        }

        return new self($attributes);
    }

    public function toString(): string
    {
        $attr = '';

        foreach ($this->attributes as $key => $value) {
            if (true === $value) {
                $attr .= ' '.$key;

                continue;
            }

            if (is_array($value)) {
                $value = self::arrayValue($value);
            }

            if (null === $value || false === $value) {
                continue;
            }

            if ($value instanceof BackedEnum) {
                $value = $value->value;
            } elseif (is_string($value)) {
                $value = rex_escape($value);
            }

            $attr .= ' '.$key.'="'.$value.'"';
        }

        return ltrim($attr);

    }

    /** @param array<string|int, string|bool> $array */
    private static function arrayValue(array $array): ?string
    {
        $array = self::normalizeArrayValue($array);

        if (!$array) {
            return null;
        }

        return implode(' ', array_keys(array_filter($array)));
    }

    /**
     * @param array<string|int, string|bool> $array
     * @return array<string, bool>
     */
    private static function normalizeArrayValue(array $array): array
    {
        $normalized = [];

        foreach ($array as $key => $value) {
            if (is_int($key)) {
                $normalized[rex_type::string($value)] = true;
                continue;
            }

            $normalized[$key] = rex_type::bool($value);
        }

        return $normalized;
    }
}
