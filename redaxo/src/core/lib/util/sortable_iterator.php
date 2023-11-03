<?php

/**
 * Sortable iterator.
 *
 * @author gharlan
 *
 * @template TKey of array-key
 * @template TValue
 * @implements IteratorAggregate<TKey, TValue>
 *
 * @package redaxo\core
 */
class rex_sortable_iterator implements IteratorAggregate
{
    public const VALUES = 1;
    public const KEYS = 2;

    /** @var Traversable<TKey, TValue> */
    private $iterator;
    /** @var self::VALUES|self::KEYS|callable(mixed,mixed):int */
    private $sort;

    /**
     * @param Traversable<TKey, TValue>  $iterator Inner iterator
     * @param self::VALUES|self::KEYS|callable(mixed,mixed):int $sort Sort mode, possible values are rex_sortable_iterator::VALUES (default), rex_sortable_iterator::KEYS or a callable
     */
    public function __construct(Traversable $iterator, $sort = self::VALUES)
    {
        $this->iterator = $iterator;
        $this->sort = $sort;
    }

    #[ReturnTypeWillChange]
    public function getIterator()
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
