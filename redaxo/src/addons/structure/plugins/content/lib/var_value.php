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

    $value = $this->getContextData()->getValue('value' . $id);

    if ($this->hasArg('isset') && $this->getArg('isset')) {
      return $value ? 'true' : 'false';
    }

    $output = $this->getArg('output');
    if ($output == 'php') {
      if ($this->environmentIs(self::ENV_BACKEND)) {
        $value = rex_string::highlight($value);
      } else {
        return 'rex_var::nothing(require rex_stream::factory(substr(__FILE__, 6) . \'/REX_VALUE/' . $id . '\', ' . self::quote($value) . '))';
      }
    } elseif ($output == 'html') {
      $value = str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $value);
    } else {
      $value = htmlspecialchars($value);
      if (!$this->environmentIs(self::ENV_INPUT)) {
        $value = nl2br($value);
      }
    }

    return self::quote($value);
  }
}
