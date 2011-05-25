<?php

class rex_package_compat
{
  private $package;

  public function __construct(rex_package_interface $package)
  {
    $this->package = $package;
  }

  public function includeFile($file)
  {
    foreach(array_keys($GLOBALS) as $global)
    {
      if($global != 'file')
      {
        global $$global;
      }
    }

    include $this->package->getBasePath($file);

    $GLOBALS += get_defined_vars();
  }

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->package, $method), $arguments);
  }
}