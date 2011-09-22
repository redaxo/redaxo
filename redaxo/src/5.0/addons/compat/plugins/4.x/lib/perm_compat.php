<?php

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