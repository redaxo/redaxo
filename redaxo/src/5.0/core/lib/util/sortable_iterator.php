<?php

/**
 * Sortable iterator
 *
 * @author gharlan
 */
class rex_sortable_iterator implements IteratorAggregate
{
  const
    VALUES = 1,
    KEYS = 2;

  private
    $iterator,
    $sort;

  /**
   * Constructor
   *
   * @param Traversable $iterator Inner iterator
   * @param int|callable $sort Sort mode, possible values are rex_sortable_iterator::VALUES (default), rex_sortable_iterator::KEYS or a callable
   */
  public function __construct(Traversable $iterator, $sort = self::VALUES)
  {
    $this->iterator = $iterator;
    $this->sort = $sort;
  }

  /* (non-PHPdoc)
   * @see IteratorAggregate::getIterator()
   */
  public function getIterator()
  {
    $array = iterator_to_array($this->iterator);
    switch($this->sort)
    {
      case self::VALUES:
        asort($array);
        break;
      case self::KEYS:
        ksort($array);
        break;
      default:
        uasort($array, $this->sort);
    }
    return new ArrayIterator($array);
  }
}