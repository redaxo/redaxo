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
  const VAR_NAME = 'REX_CONFIG';
  
  // --------------------------------- Output

  public function getTemplate($content)
  {
    return $this->matchConfig($content);
  }

  public function getBEOutput(rex_sql $sql, $content)
  {
    return $this->matchConfig($content);
  }

  /**
   * Werte für die Ausgabe
   */
  private function matchConfig($content)
  {
    $var = self::VAR_NAME;
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
      list ($param_str, $args)   = $match;
      $field       = $this->getArg('field', $args, '');

      $tpl = '<?php echo '. __CLASS__ ."::getConfig('". addslashes($field) ."', '". json_encode($args) ."'); ?>";

      $content = str_replace($var . '[' . $param_str . ']', $tpl, $content);
    }

    return $content;
  }

  /**
   * Returns the property of the given config-field, parsed using the given rex-var arguments.
   * 
   * @param string $field The name of the config field
   * @param string $args A JSON String representing the rex-var arguments
   * @return string
   */
  static public function getConfig($field, $args = '')
  {
    $config = rex::getProperty($field, rex::getConfig($field));
    $config = self::handleGlobalVarParams(self::VAR_NAME, json_decode($args, true), $config);
    return htmlspecialchars($config);
  }
}
