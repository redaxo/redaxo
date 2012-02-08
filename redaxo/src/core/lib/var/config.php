<?php

/**
 * REX_CONFIG[key=xzy]
 *
 * Arguments:
 *   - key
 *   - namespace
 *
 * @author gharlan
 *
 * @package redaxo5
 */

class rex_var_config extends rex_var
{
  protected function getOutput()
  {
    $key = $this->getArg('key', null, true);
    if($key === null)
      return false;
    $namespace = $this->getArg('namespace', "'". rex::CONFIG_NAMESPACE ."'");
    return 'htmlspecialchars(rex_config::get('. $namespace .', '. $key .'))';
  }
}
