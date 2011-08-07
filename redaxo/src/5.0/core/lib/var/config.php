<?php

/**
 * REX_CONFIG[field=xzy]
 *
 * Attribute:
 *   - field    => Feld, das ausgegeben werden soll
 *
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_var_config extends rex_var
{
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
  	global $REX;

    $var = 'REX_CONFIG';
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

  static public function getConfig($field, $args = '')
  {
    $config = rex::getProperty($field, rex::getConfig($field));
    $config = self::handleGlobalVarParams('REX_CONFIG', json_decode($args, true), $config);
    return htmlspecialchars($config);
  }
}