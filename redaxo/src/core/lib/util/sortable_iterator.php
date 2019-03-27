<?php

/**
 * Sortable iterator.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_sortable_iterator implements IteratorAggregate
{
    const VALUES = 1;
    const KEYS = 2;

    private $iterator;
    private $sort;

    /**
     * Constructor.
     *
     * @param Traversable  $iterator Inner iterator
     * @param int|callable $sort     Sort mode, possible values are rex_sortable_iterator::VALUES (default), rex_sortable_iterator::KEYS or a callable
     */
    public function __construct(Traversable $iterator, $sort = self::VALUES)
    {
        $this->iterator = $iterator;
        $this->sort = $sort;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $array = iterator_to_array($this->iterator);
        $sort = is_callable($this->sort) ? 'callback' : $this->sort;
        $normalize = function ($string) {
            $string = preg_replace("/(?<=[aou])\xcc\x88/i", '', $string);
            $string = mb_strtolower($string);
            $string = str_replace(['ä', 'ö', 'ü', 'ß'], ['a', 'o', 'u', 's'], $string);
            return $string;
        };
        $sortCallback = function ($a, $b) use ($normalize) {
            $a = $normalize($a);
            $b = $normalize($b);
            return strnatcasecmp($a, $b);
        };
        switch ($sort) {
            case self::VALUES:
                uasort($array, $sortCallback);
                break;
            case self::KEYS:
                uksort($array, $sortCallback);
                break;
            case 'callback':
                uasort($array, $this->sort);
                break;
            default:
                throw new rex_exception('Unknown sort mode!');
        }
        return new ArrayIterator($array);
    }
}
