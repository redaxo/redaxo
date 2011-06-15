<?php

class rex_compat_array extends rex implements ArrayAccess, IteratorAggregate, Countable
{
  private
    $callbackAliases = array(),
    $globalVarAliases = array();

  public function offsetExists($key)
  {
    return self::hasProperty(strtolower($key));
  }

  public function &offsetGet($key)
  {
    if(isset($this->callbackAliases[$key]['get']))
    {
      $value = call_user_func($this->callbackAliases[$key]['get']);
      return $value;
    }
    return self::$properties[strtolower($key)];
  }

  public function offsetSet($key, $value)
  {
    if(isset($this->callbackAliases[$key]['set']))
    {
      call_user_func($this->callbackAliases[$key]['set'], $value);
    }
    else
    {
      self::setProperty(strtolower($key), $value);
    }
    if(isset($this->globalVarAliases[$key]))
    {
      $var = $this->globalVarAliases[$key];
      global $$var;
      $$var = self::offsetGet($key);
    }
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

  public function setCallbackAlias($key, $get, $set)
  {
    $this->callbackAliases[$key]['get'] = $get;
    $this->callbackAliases[$key]['set'] = $set;
  }

  public function setGlobalVarAlias($key, $var)
  {
    $this->globalVarAliases[$key] = $var;
    global $$var;
    $$var = self::offsetGet($key);
  }
}