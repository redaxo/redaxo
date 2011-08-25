<?php

class rex_perm_compat implements ArrayAccess
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
}