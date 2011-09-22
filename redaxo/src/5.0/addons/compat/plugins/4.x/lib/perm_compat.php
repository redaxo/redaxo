<?php

/**
 * Compat class for $REX['PERM'], $REX['EXTPERM'] and $REX['EXTRAPERM']
 *
 * To realize statements like <code>$REX['PERM'][] = 'myperm[]'</code> it is necessary that
 * $REX['PERM'] returns a reference. But there isn't a public array of all permissions that
 * could be returned by reference, so $REX['PERM'] is an object of this compat class.
 *
 * @author gharlan
 */
class rex_perm_compat implements ArrayAccess, IteratorAggregate, Countable
{
  private $group;

  public function __construct($group)
  {
    $this->group = $group;
  }

  public function offsetExists($key)
  {
    return false;
  }

  public function offsetGet($key)
  {
    return null;
  }

  public function offsetSet($key, $value)
  {
    rex_perm::register($value, null, $this->group);
  }

  public function offsetUnset($key)
  {
  }

  public function getIterator()
  {
    return new ArrayIterator(array_keys(rex_perm::getAll($this->group)));
  }

  public function count()
  {
    return count(rex_perm::getAll($this->group));
  }
}