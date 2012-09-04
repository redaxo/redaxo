<?php

/**
 * REX_PROPERTY[key=xzy]
 *
 * Arguments:
 *   - key
 *   - namespace
 *
 * @author gharlan
 *
 * @package redaxo5
 */

class rex_var_property extends rex_var
{
  protected function getOutput()
  {
    $key = $this->getParsedArg('key', null, true);
    if ($key === null)
      return false;
    $namespace = $this->getParsedArg('namespace');
    $base = $namespace ? 'rex_package::get(' . $namespace . ')->' : 'rex::';
    return 'htmlspecialchars(' . $base . 'getProperty(' . $key . '))';
  }
}
