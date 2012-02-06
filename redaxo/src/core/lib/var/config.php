<?php

/**
 * REX_CONFIG[field=xzy]
 *
 * Attribute:
 *   - field    => Feld, das ausgegeben werden soll
 *
 *
 * @package redaxo5
 */

class rex_var_config extends rex_var
{
  protected function getOutput()
  {
    $field = $this->getArg('field', '');
    return __CLASS__ ."::getConfig('". addslashes($field) ."')";
  }

  /**
   * Returns the property of the given config-field
   *
   * @param string $field The name of the config field
   * @return string
   */
  static public function getConfig($field)
  {
    $config = rex::getProperty($field, rex::getConfig($field));
    return htmlspecialchars($config);
  }
}