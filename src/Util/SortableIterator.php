<?php

namespace Redaxo\Core\Util;

use ArrayIterator;
use Closure;
use IteratorAggregate;
use rex_exception;
use Traversable;

use function is_callable;

/**
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 */
readonly class SortableIterator implements IteratorAggregate
{
    final public const int VALUES = 1;
    final public const int KEYS = 2;

    /**
     * @param Traversable<TKey, TValue> $iterator Inner iterator
     * @param self::VALUES|self::KEYS|Closure(mixed,mixed):int $sort Sort mode, possible values are rex_sortable_iterator::VALUES (default), rex_sortable_iterator::KEYS or a callable
     */
    public function __construct(
        private Traversable $iterator,
        private int|Closure $sort = self::VALUES,
    ) {}

    public function getIterator(): Traversable
    {
        $array = iterator_to_array($this->iterator);
        $normalize = static function ($string) {
            $string = preg_replace("/(?<=[aou])\xcc\x88/i", '', $string);
            $string = mb_strtolower($string);
            return str_replace(['ä', 'ö', 'ü', 'ß'], ['a', 'o', 'u', 's'], $string);
        };
        $sortCallback = static function ($a, $b) use ($normalize) {
            $a = $normalize($a);
            $b = $normalize($b);
            return strnatcasecmp($a, $b);
        };
        match (true) {
            self::VALUES === $this->sort => uasort($array, $sortCallback),
            self::KEYS === $this->sort => uksort($array, $sortCallback),
            is_callable($this->sort) => uasort($array, $this->sort),
            default => throw new rex_exception('Unknown sort mode!'),
        };
        return new ArrayIterator($array);
    }
}
