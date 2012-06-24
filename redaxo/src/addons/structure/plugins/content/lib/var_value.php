<?php

/**
 * REX_VALUE[1],
 *
 * @package redaxo5
 */

class rex_var_value extends rex_var
{
  protected function getOutput()
  {
    $id = $this->getArg('id', 0, true);
    if ($this->getContext() != 'module' || !is_numeric($id) || $id < 1 || $id > 20) {
      return false;
    }

    return self::quote($this->getContextData()->getValue('value' . $id));
  }
}
