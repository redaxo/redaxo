<?php

class rex_compat_array extends rex implements ArrayAccess, IteratorAggregate, Countable
{
  public function offsetExists($key)
  {
    return self::hasProperty(strtolower($key));
  }

  public function &offsetGet($key)
  {
    return self::$properties[strtolower($key)];
  }

  public function offsetSet($key, $value)
  {
    self::setProperty(strtolower($key), $value);
  }

  public function offsetUnset($key)
  {
    self::removeProperty(strtolower($key));
  }

  public function getIterator()
  {
    return new ArrayIterator(self::$properties);
  }

  public function count()
  {
    return count(self::$properties);
  }
}