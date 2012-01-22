<?php


/**
 * REX_VALUE[1],
 * REX_HTML_VALUE[1],
 * REX_PHP_VALUE[1],
 * REX_PHP,
 * REX_HTML,
 * REX_IS_VALUE
 *
 * @package redaxo5
 * @version svn:$Id$
 */

class rex_var_value extends rex_var
{
  // --------------------------------- Actions

  public function getACRequestValues(array $REX_ACTION)
  {
    $values = rex_request('VALUE', 'array');
    for ($i = 1; $i < 21; $i++)
    {
      $value = isset($values[$i]) ? $values[$i] : '';

      $REX_ACTION['VALUE'][$i] = $value;
    }
    $REX_ACTION['PHP'] = rex_request('INPUT_PHP', 'string');
    $REX_ACTION['HTML'] = $this->stripPHP(rex_request('INPUT_HTML', 'string'));

    return $REX_ACTION;
  }

  public function getACDatabaseValues(array $REX_ACTION, rex_sql $sql)
  {
    for ($i = 1; $i < 21; $i++)
    {
      $REX_ACTION['VALUE'][$i] = $this->getValue($sql, 'value'. $i);
    }
    $REX_ACTION['PHP'] = $this->getValue($sql, 'php');
    $REX_ACTION['HTML'] = $this->getValue($sql, 'html');

    return $REX_ACTION;
  }

  public function setACValues(rex_sql $sql, array $REX_ACTION)
  {
    for ($i = 1; $i < 21; $i++)
    {
      $this->setValue($sql, 'value' . $i, $REX_ACTION['VALUE'][$i]);
    }

    $this->setValue($sql, 'php', $REX_ACTION['PHP']);
    $this->setValue($sql, 'html', $REX_ACTION['HTML']);
  }

  // --------------------------------- Output

  public function getBEOutput(rex_sql $sql, $content)
  {
    $content = $this->getOutput($sql, $content, true);

    $php_content = $this->getValue($sql, 'php');
    $php_content = rex_highlight_string($php_content, true);

    $content = str_replace('REX_PHP', $this->stripPHP($php_content), $content);
    return $content;
  }

  public function getBEInput(rex_sql $sql, $content)
  {
    $content = $this->getOutput($sql, $content);
    $content = str_replace('REX_PHP', htmlspecialchars($this->getValue($sql, 'php'),ENT_QUOTES), $content);
    return $content;
  }

  public function getFEOutput(rex_sql $sql, $content)
  {
    $content = $this->getOutput($sql, $content, true);
    $content = str_replace('REX_PHP', $this->getValue($sql, 'php'), $content);
    return $content;
  }

  private function getOutput(rex_sql $sql, $content, $nl2br = false)
  {
    $content = $this->matchValue($sql, $content, $nl2br);
    $content = $this->matchHtmlValue($sql, $content);
    $content = $this->matchIsValue($sql, $content);
    $content = $this->matchPhpValue($sql, $content);
    $content = str_replace('REX_HTML', $this->getValue($sql, 'html'), $content);

    return $content;
  }

  /**
   * Wert für die Ausgabe
   */
  private function _matchValue(rex_sql $sql, $content, $var, $escape = false, $nl2br = false, $stripPHP = false, $booleanize = false)
  {
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      $id = $this->getArg('id', $args, 0);

      if ($id > 0 && $id < 21)
      {
        $replace = $this->getValue($sql, 'value' . $id);
        if ($booleanize)
        {
          $replace = $replace == '' ? 'false' : 'true';
        }
        else
        {
          if ($escape)
          {
            $replace = htmlspecialchars($replace,ENT_QUOTES);
          }

          if ($nl2br)
          {
            $replace = nl2br($replace);
          }

          if ($stripPHP)
          {
            $replace = $this->stripPHP($replace);
          }
        }

        $replace = $this->handleGlobalVarParams($var, $args, $replace);
        $content = str_replace($var . '[' . $param_str . ']', $replace, $content);
      }
    }

    return $content;
  }

  private function matchValue(rex_sql $sql, $content, $nl2br = false)
  {
    return $this->_matchValue($sql, $content, 'REX_VALUE', true, $nl2br);
  }

  private function matchHtmlValue(rex_sql $sql, $content)
  {
    return $this->_matchValue($sql, $content, 'REX_HTML_VALUE', false, false, true);
  }

  private function matchPhpValue(rex_sql $sql, $content)
  {
    return $this->_matchValue($sql, $content, 'REX_PHP_VALUE', false, false, false);
  }

  private function matchIsValue(rex_sql $sql, $content)
  {
    return $this->_matchValue($sql, $content, 'REX_IS_VALUE', false, false, false, true);
  }
}