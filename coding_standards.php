#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli')
{
  echo 'error: this script may only be run from CLI', PHP_EOL;
  exit(1);
}

// https://github.com/symfony/symfony/blob/f53297681a7149f2a809da12ea3a8b8cfd4d3025/src/Symfony/Component/Console/Output/StreamOutput.php#L103-112
$hasColorSupport = DIRECTORY_SEPARATOR == '\\' ? getenv('ANSICON') !== false : function_exists('posix_isatty') && @posix_isatty(STDOUT);

echo PHP_EOL;
echo $hasColorSupport ? "\033[1;37m\033[45m" : '';
echo 'REDAXO CODING STANDARDS CHECK';
echo $hasColorSupport ? "\033[0m" : '';
echo PHP_EOL, PHP_EOL;

if (!isset($argv[1]) || !in_array($argv[1], array('fix', 'check')))
{
  echo 'Usage:
  php ', $argv[0], ' <mode> [options]
  php ', $argv[0], ' <mode> <path> [options]
  php ', $argv[0], ' <mode> core [options]
  php ', $argv[0], ' <mode> package <package> [options]

  <mode>     "check" or "fix"
  <path>     path to a subdirectory
  <package>  package id ("addonname" or "addonname/pluginname")

  options:
    --hide-process: Don\'t show current checking file path', PHP_EOL, PHP_EOL;

  exit(1);
}

$fix = $argv[1] == 'fix';

$dir = __DIR__;
if (isset($argv[2]) && $argv[2][0] !== '-')
{
  if ($argv[2] == 'core')
  {
    $dir .= '/redaxo/src/core';
    if (!is_dir($dir))
    {
      echo 'ERROR: Core directory does not exist!', PHP_EOL, PHP_EOL;
      exit(1);
    }
  }
  elseif ($argv[2] == 'package')
  {
    if (!isset($argv[3]) || $argv[3][0] === '-')
    {
      echo 'ERROR: Missing package id!', PHP_EOL, PHP_EOL;
      exit(1);
    }
    $package = $argv[3];
    if (strpos($package, '/') === false)
    {
      $dir .= '/redaxo/src/addons/' . $package;
    }
    else
    {
      list($addon, $plugin) = explode('/', $package, 2);
      $dir .= '/redaxo/src/addons/' . $addon . '/plugins/' . $plugin;
    }
    if (!is_dir($dir))
    {
      echo 'ERROR: Package "', $package, '" does not exist!', PHP_EOL, PHP_EOL;
      exit(1);
    }
  }
  else
  {
    $dir .= '/' . $argv[2];
    if (!is_dir($dir))
    {
      echo 'ERROR: Directory "', $argv[2], '" does not exist!', PHP_EOL, PHP_EOL;
      exit(1);
    }
  }
}

class rex_coding_standards_fixer
{
  protected
    $content,
    $fixable = array(),
    $nonFixable = array();

  public function __construct($content)
  {
    $this->content = $content;

    $this->fix();
  }

  public function hasChanged()
  {
    return !empty($this->fixable) || !empty($this->nonFixable);
  }

  public function getFixable()
  {
    return array_keys($this->fixable);
  }

  public function getNonFixable()
  {
    return array_keys($this->nonFixable);
  }

  public function getResult()
  {
    return $this->content;
  }

  protected function addFixable($fixable)
  {
    $this->fixable[$fixable] = true;
  }

  protected function addNonFixable($nonFixable)
  {
    $this->nonFixable[$nonFixable] = true;
  }

  protected function fix()
  {
    if (($encoding = mb_detect_encoding($this->content, 'UTF-8,ISO-8859-1,WINDOWS-1252')) != 'UTF-8')
    {
      if ($encoding === false)
      {
        $encoding = mb_detect_encoding($this->content);
      }
      if ($encoding !== false)
      {
        $this->content = iconv($encoding, 'UTF-8', $this->content);
        $this->addFixable('fix encoding from ' . $encoding . ' to UTF-8');
      }
      else
      {
        $this->addNonFixable('couldn\'t detect encoding, change it to UTF-8');
      }
    }
    elseif (strpos($this->content, "\xEF\xBB\xBF") === 0)
    {
      $this->content = substr($this->content, 3);
      $this->addFixable('remove BOM (Byte Order Mark)');
    }

    if (strpos($this->content, "\r") !== false)
    {
      $this->content = str_replace(array("\r\n", "\r"), "\n", $this->content);
      $this->addFixable('fix line endings to LF');
    }

    if (strpos($this->content, "\t") !== false)
    {
      $this->content = str_replace("\t", '  ', $this->content);
      $this->addFixable('convert tabs to spaces');
    }

    if (preg_match('/ $/m', $this->content))
    {
      $this->content = preg_replace('/ +$/m', '', $this->content);
      $this->addFixable('remove trailing whitespace');
    }

    if (strlen($this->content) && substr($this->content, -1) != "\n")
    {
      $this->content .= "\n";
      $this->addFixable('add newline at end of file');
    }

    if (preg_match("/\n{2,}$/", $this->content))
    {
      $this->content = rtrim($this->content, "\n") . "\n";
      $this->addFixable('remove multiple newlines at end of file');
    }
  }
}

class rex_coding_standards_fixer_php extends rex_coding_standards_fixer
{
  const
    MSG_LOWERCASE_CONTROL_KEYWORD = 'replace control keywords by their lowercase variants',
    MSG_SPACE_AFTER_CONTROL_KEYWORD = 'add space after control keywords ("if", "for" etc.)',
    MSG_SPACES_AROUND_BINARY_OPERATOR = 'add spaces around binary operators ("=", "+", "&&" etc.)';

  protected
    $checkNamingConventions,
    $tokens,
    $index,
    $previous,
    $indentation = '',
    $isTernary = 0;

  public function __construct($content, $checkNamingConventions = true)
  {
    $this->checkNamingConventions = $checkNamingConventions;
    parent::__construct($content);
  }

  protected function fix()
  {
    parent::fix();

    $this->content = preg_replace('/<\?(?=\s)/', '<?php', $this->content, -1, $count);
    if ($count)
    {
      $this->addFixable('replace php short open tags "<?" by "<?php"');
    }

    $this->content = preg_replace("/\n* *\?>$/", '', $this->content, -1, $count);
    if ($count)
    {
      $this->addFixable('remove php closing tag "?>" at end of file');
    }

    $this->tokens = token_get_all($this->content);
    $this->content = '';
    $count = count($this->tokens);
    for ($this->index = 0; $this->index < $count; $this->index++)
    {
      $this->fixToken(new rex_php_token($this->tokens[$this->index]));
    }
    $this->content = preg_replace('/ +$/m', '', $this->content);
  }

  protected function addToken(rex_php_token $token)
  {
    $this->previous = $token;
    $this->content .= $token->text;
  }

  protected function previousToken()
  {
    return $this->previous;
  }

  protected function nextToken()
  {
    $this->index++;
    if (isset($this->tokens[$this->index]))
    {
      return new rex_php_token($this->tokens[$this->index]);
    }
    return null;
  }

  protected function decrementTokenIndex()
  {
    $this->index--;
  }

  protected function fixToken(rex_php_token $token)
  {
    switch ($token->type)
    {
      case T_STRING:
        if (in_array(strtolower($token->text), array('true', 'false', 'null')))
        {
          $this->checkLowercase($token, 'replace boolean/null constants by their lowercase variants ("true", "false" and "null")');
        }
        $this->addToken($token);
        break;

      case T_CONSTANT_ENCAPSED_STRING:
        if (preg_match('/^"([^${\'\\\\]*)"$/', $token->text, $match))
        {
          $token->text = "'" . $match[1] . "'";
          $this->addFixable('replace double quotes around simple strings by single quotes');
        }
        $this->addToken($token);
        break;

      case T_IF:
      case T_FOR:
      case T_FOREACH:
      case T_WHILE:
      case T_SWITCH:
      case T_CASE:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_AFTER_CONTROL_KEYWORD);
        break;

      case T_ELSE:
        $next = $this->nextToken();
        if ($next->type === T_WHITESPACE)
        {
          $nextNext = $this->nextToken();
          if ($nextNext->type === T_IF)
          {
            $this->addFixable('replace "else if" by "elseif"');
            $this->fixToken(new rex_php_token(T_ELSEIF, 'elseif'));
            break;
          }
          $this->decrementTokenIndex();
        }
        $this->decrementTokenIndex();
        $this->checkNewlineBefore();
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        break;

      case T_ELSEIF:
      case T_CATCH:
        $this->checkNewlineBefore();
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_AFTER_CONTROL_KEYWORD);
        break;

      case T_WHITESPACE:
        if (($pos = strrpos($token->text, "\n")) !== false)
        {
          $this->indentation = substr($token->text, $pos + 1);
        }
        $this->addToken($token);
        break;

      case T_COMMENT:
        if (substr($token->text, -1) === "\n")
        {
          $token->text = substr($token->text, 0, -1);
          $this->addToken($token);
          $next = $this->nextToken();
          if ($next && $next->type === T_WHITESPACE)
          {
            $next->text = "\n" . $next->text;
            $this->fixToken($next);
          }
          else
          {
            $this->addToken(new rex_php_token(T_WHITESPACE, "\n"));
            $this->decrementTokenIndex();
          }
          break;
        }
        $this->addToken($token);
        break;

      case T_ABSTRACT:
      case T_ARRAY:
      case T_AS:
      case T_BREAK:
      case T_CALLABLE:
      case T_CLONE:
      case T_CONTINUE:
      case T_DECLARE:
      case T_DEFAULT:
      case T_DO:
      case T_ECHO:
      case T_EMPTY:
      case T_ENDDECLARE:
      case T_ENDFOR:
      case T_ENDFOREACH:
      case T_ENDIF:
      case T_ENDSWITCH:
      case T_ENDWHILE:
      case T_EVAL:
      case T_EXIT:
      case T_EXTENDS:
      case T_FINAL:
      case T_FUNCTION:
      case T_GLOBAL:
      case T_GOTO:
      case T_IMPLEMENTS:
      case T_INCLUDE:
      case T_INCLUDE_ONCE:
      case T_INSTANCEOF:
      case T_INSTEADOF:
      case T_ISSET:
      case T_LIST:
      case T_NAMESPACE:
      case T_NEW:
      case T_PRINT:
      case T_PRIVATE:
      case T_PUBLIC:
      case T_PROTECTED:
      case T_REQUIRE:
      case T_REQUIRE_ONCE:
      case T_RETURN:
      case T_STATIC:
      case T_THROW:
      case T_TRY:
      case T_UNSET:
      case T_USE:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        break;

      case T_CLASS:
      case T_INTERFACE:
      case T_TRAIT:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);

        if ($this->checkNamingConventions)
        {
          $this->skipWhitespace();
          $next = $this->nextToken();
          if ($next->type === T_STRING)
          {
            if (strpos($next->text, 'rex_') !== 0 && $next->text !== 'rex')
            {
              $this->addNonFixable('use "rex_" prefix for class/interface names');
            }
            if (strtolower($next->text) !== $next->text)
            {
              $this->addNonFixable('use only lowercase and underscores in class/interface names');
            }
          }
          $this->decrementTokenIndex();
        }
        break;

      case T_CONST:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        if ($this->checkNamingConventions)
        {
          $semicolon = new rex_php_token(rex_php_token::SIMPLE, ';');
          $comma = new rex_php_token(rex_php_token::SIMPLE, ',');
          do
          {
            $this->skipWhitespace();
            $next = $this->nextToken();
            if ($next->type === T_STRING && strtoupper($next->text) !== $next->text)
            {
              $this->addNonFixable('use only uppercase and underscores for constants');
              $this->fixToken($next);
              break;
            }
            $this->fixToken($next);
            while (!in_array($next = $this->nextToken(), array($comma, $semicolon)))
            {
              $this->fixToken($next);
            }
            $this->fixToken($next);
          }
          while ($next != $semicolon);
        }
        break;

      case T_VAR:
        $this->addToken($token);
        $this->addNonFixable('replace old "var $property;" syntax by using visibilities');
        break;

      case T_CLASS_C:
      case T_DIR:
      case T_FILE:
      case T_FUNC_C:
      case T_LINE:
      case T_METHOD_C:
      case T_NS_C:
      case T_TRAIT_C:
        $this->checkUppercase($token, 'replace "__*__" constants by their uppercase variants');
        $this->addToken($token);
        break;

      case T_ARRAY_CAST:
      case T_BOOL_CAST:
      case T_DOUBLE_CAST:
      case T_INT_CAST:
      case T_OBJECT_CAST:
      case T_STRING_CAST:
      case T_UNSET_CAST:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $trim = '(' . trim($token->text, '() ') . ')';
        if ($trim !== $token->text)
        {
          $token->text = $trim;
          $this->addFixable('remove spaces within type casts');
        }
        $this->addToken($token);
        $this->checkSpaceBehind('add space after type casting');
        break;

      case T_AND_EQUAL:
      case T_BOOLEAN_AND:
      case T_BOOLEAN_OR:
      case T_CONCAT_EQUAL:
      case T_DIV_EQUAL:
      case T_DOUBLE_ARROW:
      case T_IS_EQUAL:
      case T_IS_GREATER_OR_EQUAL:
      case T_IS_IDENTICAL:
      case T_IS_NOT_EQUAL:
      case T_IS_NOT_IDENTICAL:
      case T_IS_SMALLER_OR_EQUAL:
      case T_MINUS_EQUAL:
      case T_MOD_EQUAL:
      case T_MUL_EQUAL:
      case T_OR_EQUAL:
      case T_PLUS_EQUAL:
      case T_SL:
      case T_SL_EQUAL:
      case T_SR:
      case T_SR_EQUAL:
      case T_XOR_EQUAL:
        $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
        break;

      case T_LOGICAL_AND:
      case T_LOGICAL_OR:
      case T_LOGICAL_XOR:
        $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
        break;

      case rex_php_token::SIMPLE:
        switch ($token->text)
        {
          case '?':
            $next = $this->nextToken();
            if ($next->type === rex_php_token::SIMPLE && $next->text === ':')
            {
              $token->text .= ':';
            }
            else
            {
              $this->decrementTokenIndex();
              $this->isTernary++;
            }
            $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            $this->addToken($token);
            $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            return;

          case ':':
            if ($this->isTernary)
            {
              $this->isTernary--;
              $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
              $this->addToken($token);
              $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
              return;
            }
            $this->addToken($token);
            return;

          case '=':
          case '.':
          case '*':
          case '/':
          case '%':
          case '<':
          case '>':
          case '|':
          case '^':
          // todo: check around +, -, & (problem: they can also be unary operators)
            $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            $this->addToken($token);
            $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            return;

          case ',':
          case ';':
            $this->addToken($token);
            $this->checkSpaceBehind('add space after "," and ";"');
            return;

          case '{':
            $next = $this->nextToken();
            if ($next->type === rex_php_token::SIMPLE && $next->text === '}')
            {
              $this->addToken($token);
              $this->addToken($next);
              return;
            }
            elseif ($next->type === T_WHITESPACE && preg_match('/^ +$/D', $next->text))
            {
              $nextNext = $this->nextToken();
              if ($nextNext->type === rex_php_token::SIMPLE && $nextNext->text === '}')
              {
                $this->addToken($token);
                $this->addToken($next);
                $this->addToken($nextNext);
                return;
              }
              $this->decrementTokenIndex();
            }
            $this->checkNewlineBefore();
            $this->addToken($token);
            $this->decrementTokenIndex();
            $this->skipWhitespace(false);
            $next = $this->nextToken();
            $this->decrementTokenIndex();
            if (!$this->isNewline($next))
            {
              $this->addFixable('add newline after opening brace');
              $this->fixToken(new rex_php_token(T_WHITESPACE, "\n" . $this->indentation . '  '));
            }
            return;

          case '}':
            $this->checkNewlineBefore(true);
            $this->addToken($token);
            return;

          default:
            $this->addToken($token);
        }
        break;

      default:
        $this->addToken($token);
    }
  }

  private function skipWhitespace($skipNewlines = true)
  {
    $next = $this->nextToken();
    while (($next->type === T_WHITESPACE || $next->type === T_COMMENT) && ($skipNewlines || strpos($next->text, "\n") === false))
    {
      $this->addToken($next);
      $next = $this->nextToken();
    }
    $this->decrementTokenIndex();
  }

  private function checkLowercase(rex_php_token $token, $msg)
  {
    $lowercaseText = strtolower($token->text);
    if ($lowercaseText !== $token->text)
    {
      $token->text = $lowercaseText;
      $this->addFixable($msg);
    }
  }

  private function checkUppercase(rex_php_token $token, $msg)
  {
    $uppercaseText = strtoupper($token->text);
    if ($uppercaseText !== $token->text)
    {
      $token->text = $uppercaseText;
      $this->addFixable($msg);
    }
  }

  private function checkSpaceBefore($msg)
  {
    if ($this->previousToken()->type !== T_WHITESPACE)
    {
      $this->addToken(new rex_php_token(T_WHITESPACE, ' '));
      $this->addFixable($msg);
    }
  }

  private function checkSpaceBehind($msg)
  {
    $next = $this->nextToken();
    if ($next->type !== T_WHITESPACE)
    {
      $this->addToken(new rex_php_token(T_WHITESPACE, ' '));
      $this->addFixable($msg);
    }
    $this->decrementTokenIndex();
  }

  private function checkNewlineBefore($indentationBack = false)
  {
    $previous = $this->previousToken();
    if (!$this->isNewline($previous))
    {
      if ($indentationBack)
      {
        $this->indentation = substr($this->indentation, 0, -2);
      }
      $this->addToken(new rex_php_token(T_WHITESPACE, "\n" . $this->indentation));
      $this->addFixable('add newline before braces and control keywords ("if", "for" etc.)');
    }
  }

  private function isNewline(rex_php_token $token)
  {
    return $token->type === T_WHITESPACE && strpos($token->text, "\n") !== false;
  }
}

// PHP 5.4 constants
defined('T_CALLABLE')  ?: define('T_CALLABLE', -10);
defined('T_INSTEADOF') ?: define('T_INSTEADOF', -11);
defined('T_TRAIT')     ?: define('T_TRAIT', -12);
defined('T_TRAIT_C')   ?: define('T_TRAIT_C', -13);

class rex_php_token
{
  const
    SIMPLE = -1;

  public
    $type,
    $text;

  public function __construct($token, $text = null)
  {
    if ($text)
    {
      $this->type = $token;
      $this->text = $text;
    }
    elseif (is_string($token))
    {
      $this->type = self::SIMPLE;
      $this->text = $token;
    }
    else
    {
      $this->type = $token[0];
      $this->text = $token[1];
    }
  }
}

$hideProcess = in_array('--hide-process', $argv);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$textExtensions = array('css', 'htaccess', 'html', 'js', 'json', 'lang', 'php', 'sql', 'textile', 'tpl', 'txt', 'yml');
$countFiles = 0;
$countFixable = 0;
$countNonFixable = 0;
foreach ($iterator as $path => $file)
{
  /* @var $file SplFileInfo */
  $subPath = $iterator->getInnerIterator()->getSubPathName();
  $fileExt = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
  if (!in_array($fileExt, $textExtensions)
    || strpos(DIRECTORY_SEPARATOR . $subPath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false)
  {
    continue;
  }

  if (!$hideProcess)
  {
    $checkString = $subPath;
    if (mb_strlen($checkString) > 60)
    {
      $checkString = substr($checkString, 0, 20) . '...' . substr($checkString, -37);
    }
    echo $checkString = 'check ' . $checkString . ' ...';
  }

  $countFiles++;
  if ($fileExt == 'php')
  {
    $checkNamingConventions = strpos($path, DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'compat' . DIRECTORY_SEPARATOR) === false;
    $fixer = new rex_coding_standards_fixer_php(file_get_contents($path), $checkNamingConventions);
  }
  else
  {
    $fixer = new rex_coding_standards_fixer(file_get_contents($path));
  }

  if (!$hideProcess)
  {
    echo str_repeat("\010 \010", mb_strlen($checkString));
  }

  if ($fixer->hasChanged())
  {
    echo $subPath, ':', PHP_EOL;
    if ($fixable = $fixer->getFixable())
    {
      echo '  > ', implode(PHP_EOL . '  > ', $fixable), PHP_EOL;
      $countFixable++;
    }
    if ($nonFixable = $fixer->getNonFixable())
    {
      echo '  ! ', implode(PHP_EOL . '  ! ', $nonFixable), PHP_EOL;
      $countNonFixable++;
    }
    echo PHP_EOL;

    if ($fix)
    {
      file_put_contents($path, $fixer->getResult());
    }
  }
}

echo '-----------------------------------', PHP_EOL;
echo 'checked ', $countFiles, ' files', PHP_EOL;
if ($countFixable)
{
  echo '', ($fix ? 'fixed' : 'found fixable'), ' problems in ', $countFixable, ' files', PHP_EOL;
}
if ($countNonFixable)
{
  echo 'found non-fixable problems in ', $countNonFixable, ' files', PHP_EOL;
}

echo PHP_EOL;
if ($hasColorSupport)
{
  echo ($countNonFixable + ($fix ? 0 : $countFixable)) ? "\033[1;37;41m" : "\033[1;30;42m";
}
echo 'FINISHED, ', !$countFixable && !$countNonFixable ? 'no problems' : 'found problems';
echo $hasColorSupport ? "\033[0m" : '';
echo PHP_EOL, PHP_EOL;

exit ($countNonFixable + ($fix ? 0 : $countFixable));
