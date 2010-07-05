<?php


/**
 * REX_VALUE[1],
 * REX_HTML_VALUE[1],
 * REX_PHP_VALUE[1],
 * REX_PHP,
 * REX_HTML,
 * REX_IS_VALUE
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_var_value extends rex_var
{
  // --------------------------------- Actions

  /*public*/ function getACRequestValues($REX_ACTION)
  {
    $values = rex_request('VALUE', 'array');
    for ($i = 1; $i < 21; $i++)
    {
      $value = isset($values[$i]) ? stripslashes($values[$i]) : '';

      $REX_ACTION['VALUE'][$i] = $value;
    }
    $REX_ACTION['PHP'] = stripslashes(rex_request('INPUT_PHP', 'string'));
    $REX_ACTION['HTML'] = $this->stripPHP(stripslashes(rex_request('INPUT_HTML', 'string')));

    return $REX_ACTION;
  }

  /*public*/ function getACDatabaseValues($REX_ACTION, & $sql)
  {
    for ($i = 1; $i < 21; $i++)
    {
      $REX_ACTION['VALUE'][$i] = $this->getValue($sql, 'value'. $i);
    }
    $REX_ACTION['PHP'] = $this->getValue($sql, 'php');
    $REX_ACTION['HTML'] = $this->getValue($sql, 'html');

    return $REX_ACTION;
  }

  /*public*/ function setACValues(& $sql, $REX_ACTION, $escape = false)
  {
    global $REX;

    for ($i = 1; $i < 21; $i++)
    {
      $this->setValue($sql, 'value' . $i, $REX_ACTION['VALUE'][$i], $escape);
    }

    $this->setValue($sql, 'php', $REX_ACTION['PHP'], $escape);
    $this->setValue($sql, 'html', $REX_ACTION['HTML'], $escape);
  }

  // --------------------------------- Output

  /*public*/ function getBEOutput(& $sql, $content)
  {
    $content = $this->getOutput($sql, $content, true);

    $php_content = $this->getValue($sql, 'php');
    $php_content = rex_highlight_string($php_content, true);

    $content = str_replace('REX_PHP', $this->stripPHP($php_content), $content);
    return $content;
  }

  /*public*/ function getBEInput(& $sql, $content)
  {
    $content = $this->getOutput($sql, $content);
    $content = str_replace('REX_PHP', htmlspecialchars($this->getValue($sql, 'php'),ENT_QUOTES), $content);
    return $content;
  }

  /*public*/ function getFEOutput(& $sql, $content)
  {
    $content = $this->getOutput($sql, $content, true);
    $content = str_replace('REX_PHP', $this->getValue($sql, 'php'), $content);
    return $content;
  }

  /*public*/ function getOutput(& $sql, $content, $nl2br = false)
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
  /*private*/ function _matchValue(& $sql, $content, $var, $escape = false, $nl2br = false, $stripPHP = false, $booleanize = false)
  {
    $matches = $this->getVarParams($content, $var);

    foreach ($matches as $match)
    {
      list ($param_str, $args) = $match;
      list ($id, $args) = $this->extractArg('id', $args, 0);
      
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

  /*private*/ function matchValue(& $sql, $content, $nl2br = false)
  {
    return $this->_matchValue($sql, $content, 'REX_VALUE', true, $nl2br);
  }

  /*private*/ function matchHtmlValue(& $sql, $content)
  {
    return $this->_matchValue($sql, $content, 'REX_HTML_VALUE', false, false, true);
  }

  /*private*/ function matchPhpValue(& $sql, $content)
  {
    return $this->_matchValue($sql, $content, 'REX_PHP_VALUE', false, false, false);
  }

  /*private*/ function matchIsValue(& $sql, $content)
  {
    return $this->_matchValue($sql, $content, 'REX_IS_VALUE', false, false, false, true);
  }
}