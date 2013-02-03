<?php

/**
 * Compat class for packages to simulate global varibale scope in included package files
 *
 * @author gharlan
 */
class rex_package_compat
{
  private $package;

  public function __construct(rex_package_interface $package)
  {
    $this->package = $package;
  }

  public function includeFile($file)
  {
    extract($GLOBALS, EXTR_SKIP);

    include $this->package->getPath($file);

    $GLOBALS = array_merge($GLOBALS, get_defined_vars());
  }

  public function __call($method, $arguments)
  {
    return call_user_func_array(array($this->package, $method), $arguments);
  }
}
