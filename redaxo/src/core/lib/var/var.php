<?php

/**
* Abstract baseclass for REX_VARS
*
* @package redaxo5
*/
abstract class rex_var
{
  const
    FRONTEND = 1,
    BACKEND = 2,
    INPUT = 4,
    OUTPUT = 8;

  static private
    $vars = array(),
    $env = null,
    $context = null;

  private
    $args = array();

  /**
   * @param string $var
   * @return self
   */
  static public function getVar($var)
  {
    if(!isset(self::$vars[$var]))
    {
      $class = 'rex_var_'. strtolower(substr($var, 4));
      if(!is_subclass_of($class, __CLASS__))
        return false;
      self::$vars[$var] = $class;
    }
    $class = self::$vars[$var];
    return new $class;
  }

  static public function parse($content, $env = null, $context = null)
  {
    if(($env & self::INPUT) != self::INPUT)
      $env = $env | self::OUTPUT;
    self::$env = $env;
    self::$context = $context;

    $tokens = token_get_all($content);
    $countTokens = count($tokens);
    $content = '';
    for($i = 0; $i < $countTokens; ++$i)
    {
      $token = $tokens[$i];
      if(is_string($token))
      {
        $add = $token;
      }
      else
      {
        $add = $token[1];
        if(in_array($token[0], array(T_INLINE_HTML, T_CONSTANT_ENCAPSED_STRING, T_STRING, T_START_HEREDOC)))
        {
          $useVariables = false;
          switch($token[0])
          {
            case T_INLINE_HTML:
              $format = '<?php echo %s; ?>';
              break;
            case T_CONSTANT_ENCAPSED_STRING:
              $format = $token[1][0] == '"' ? '". %s ."' : "'. %s .'";
              break;
            case T_STRING:
              while(isset($tokens[++$i])
                  && (is_string($tokens[$i]) && in_array($tokens[$i], array('=', '[', ']'))
                      || in_array($tokens[$i][0], array(T_WHITESPACE, T_STRING, T_CONSTANT_ENCAPSED_STRING, T_LNUMBER))))
              {
                $add .= is_string($tokens[$i]) ? $tokens[$i] : $tokens[$i][1];
              }
              --$i;
              $format = '%s';
              break;
            case T_START_HEREDOC:
              while(isset($tokens[++$i]) && (is_string($tokens[$i]) || $tokens[$i][0] != T_END_HEREDOC))
              {
                $add .= is_string($tokens[$i]) ? $tokens[$i] : $tokens[$i][1];
              }
              --$i;
              if($token[1][3] == "'") // nowdoc
              {
                $identifier = substr(rtrim($token[1], "'\r\n"), 4);
                $format = PHP_EOL . $identifier . PHP_EOL .". %s . <<<'$identifier'". PHP_EOL;
              }
              else // heredoc
              {
                $format = '{%s}';
                $useVariables = true;
              }
              break;
          }
          $add = self::replaceVars($add, $format, $useVariables);
        }
      }
      $content .= $add;
    }
    return $content;
  }

  static private function replaceVars($content, $format = '%s', $useVariables = false)
  {
    $matches = array();
    preg_match_all('/(REX_[A-Z_]+)\[((?:[^\[\]]|\\\\[\[\]]|(?R))*)\]/s', $content, $matches, PREG_SET_ORDER);
    $variables = array();
    $i = 0;
    foreach($matches as $match)
    {
      $var = self::getVar($match[1]);
      if($var === false)
        continue;
      $var->setArgs($match[2]);
      if(($output = $var->getGlobalArgsOutput()) !== false)
      {
        $output .= str_repeat("\n", max(0, substr_count($match[0], "\n") - substr_count($output, "\n") - substr_count($format, "\n")));
        if($useVariables)
        {
          $replace = '$__rex_var_content_'. $i;
          $variables[] = $replace .' = '. $output;
        }
        else
        {
          $replace = $output;
        }
        $content = str_replace($match[0], sprintf($format, $replace), $content);
        ++$i;
      }
    }
    if($useVariables && !empty($variables))
    {
      $content = 'rex_var::nothing('. implode(', ', $variables) .') . '. $content;
    }
    return $content;
  }

  private function setArgs($arg_string)
  {
    $this->args = rex_string::split($arg_string);
  }

  protected function hasArg($key, $defaultArg = false)
  {
    return isset($this->args[$key]) || $defaultArg && isset($this->args[0]);
  }

  protected function getArg($key, $default = null, $defaultArg = false)
  {
    if(!$this->hasArg($key, $defaultArg))
      return $default;
    $arg = isset($this->args[$key]) ? $this->args[$key] : $this->args[0];
    $begin = '<<<addslashes>>>';
    $end = '<<</addslashes>>>';
    $arg = $begin . self::replaceVars($arg, $end ."'. %s .'". $begin) . $end;
    $replace = array('\[' => '[', '\]' => ']');
    $callback = function($match) use($replace)
    {
      return addcslashes(strtr($match[1], $replace), "\'");
    };
    $arg = preg_replace_callback("@$begin(.*)$end@Us", $callback, $arg);
    return is_numeric($arg) ? $arg : "'$arg'";
  }

  protected function environmentIs($env)
  {
    return (self::$env & $env) == $env;
  }

  protected function getContext()
  {
    return self::$context;
  }

  abstract protected function getOutput();

  private function getGlobalArgsOutput()
  {
    if(($content = $this->getOutput()) === false)
      return false;

    if($this->hasArg('callback'))
    {
      $args = array("'subject' => ". $content);
      foreach(array('instead', 'ifempty', 'prefix', 'suffix') as $key)
      {
        if($this->hasArg($key))
        {
          $args[] = "'$key' => ". $this->getArg($key);
        }
      }
      $args = 'array('. implode(', ', $args) .')';
      return 'call_user_func('. $this->getArg('callback') .', '. $args .')';
    }

    $prefix = $this->hasArg('prefix') ? $this->getArg('prefix') .'. ' : '';
    $suffix = $this->hasArg('suffix') ? ' .'. $this->getArg('suffix') : '';
    $instead = $this->hasArg('instead');
    $ifempty = $this->hasArg('ifempty');
    if($prefix || $suffix || $instead || $ifempty)
    {
      if($instead)
      {
        $if = $content;
        $then = $this->getArg('instead');
      }
      else
      {
        $if = '$__rex_var_content = '. $content;
        $then = '$__rex_var_content';
      }
      if($ifempty)
      {
        return $prefix .'(('. $if .') ? '. $then .' : '. $this->getArg('ifempty') .')'. $suffix;
      }
      return '(('. $if .') ? '. $prefix . $then . $suffix ." : '')";
    }
    return $content;
  }

  /**
   * Handle all global arguments
   *
   * @param string $value The value of the variable
   * @param array $args The array of global arguments
   *
   * @return string The parsed variable value
   */
  static public function handleGlobalArgs($value, array $args)
  {
    if(isset($args['callback']))
    {
      $args['subject'] = $value;
      return call_user_func($args['callback'], $args);
    }

    $prefix = '';
    $suffix = '';

    if(isset($args['instead']) && $value != '')
      $value = $args['instead'];

    if(isset($args['ifempty']) && $value == '')
      $value = $args['ifempty'];

    if($value != '' && isset($args['prefix']))
      $prefix = $args['prefix'];

    if($value != '' && isset($args['suffix']))
      $suffix = $args['suffix'];

    return $prefix . $value . $suffix;
  }

  static public function nothing()
  {
    return '';
  }
}
