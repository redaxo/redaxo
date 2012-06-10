#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli') {
  echo 'error: this script may only be run from CLI', PHP_EOL;
  exit(1);
}

// https://github.com/symfony/symfony/blob/f53297681a7149f2a809da12ea3a8b8cfd4d3025/src/Symfony/Component/Console/Output/StreamOutput.php#L103-112
$hasColorSupport = getenv('ANSICON') !== false || DIRECTORY_SEPARATOR != '\\' && function_exists('posix_isatty') && @posix_isatty(STDOUT);

echo PHP_EOL;
echo $hasColorSupport ? "\033[1;37m\033[45m" : '';
echo 'REDAXO CODING STANDARDS CHECK';
echo $hasColorSupport ? "\033[0m" : '';
echo PHP_EOL, PHP_EOL;

if (!isset($argv[1]) || !in_array($argv[1], array('fix', 'check'))) {
  echo 'Usage:
  php ', $argv[0], ' <mode> [options]
  php ', $argv[0], ' <mode> <path> [options]
  php ', $argv[0], ' <mode> core [options]
  php ', $argv[0], ' <mode> package <package> [options]

  <mode>     "check" or "fix"
  <path>     path to a directory or a file
  <package>  package id ("addonname" or "addonname/pluginname")

  options:
    --hide-process: Don\'t show current checking file path', PHP_EOL, PHP_EOL;

  exit(1);
}

$fix = $argv[1] == 'fix';

$dir = __DIR__;
$file = null;
if (isset($argv[2]) && $argv[2][0] !== '-') {
  if ($argv[2] == 'core') {
    $dir .= DIRECTORY_SEPARATOR . 'redaxo' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'core';
    if (!is_dir($dir)) {
      echo 'ERROR: Core directory does not exist!', PHP_EOL, PHP_EOL;
      exit(1);
    }
  } elseif ($argv[2] == 'package') {
    if (!isset($argv[3]) || $argv[3][0] === '-') {
      echo 'ERROR: Missing package id!', PHP_EOL, PHP_EOL;
      exit(1);
    }
    $package = $argv[3];
    if (strpos($package, '/') === false) {
      $dir .= DIRECTORY_SEPARATOR . 'redaxo' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . $package;
    } else {
      list($addon, $plugin) = explode('/', $package, 2);
      $dir .= DIRECTORY_SEPARATOR . 'redaxo' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin;
    }
    if (!is_dir($dir)) {
      echo 'ERROR: Package "', $package, '" does not exist!', PHP_EOL, PHP_EOL;
      exit(1);
    }
  } else {
    if (is_dir($argv[2])) {
      $dir = $argv[2];
    } elseif (is_file($argv[2])) {
      $file = $argv[2];
      $dir = null;
    } else {
      echo 'ERROR: Directory or file "', $argv[2], '" does not exist!', PHP_EOL, PHP_EOL;
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
    if (($encoding = mb_detect_encoding($this->content, 'UTF-8,ISO-8859-1,WINDOWS-1252')) != 'UTF-8') {
      if ($encoding === false) {
        $encoding = mb_detect_encoding($this->content);
      }
      if ($encoding !== false) {
        $this->content = iconv($encoding, 'UTF-8', $this->content);
        $this->addFixable('fix encoding from ' . $encoding . ' to UTF-8');
      } else {
        $this->addNonFixable('couldn\'t detect encoding, change it to UTF-8');
      }
    } elseif (strpos($this->content, "\xEF\xBB\xBF") === 0) {
      $this->content = substr($this->content, 3);
      $this->addFixable('remove BOM (Byte Order Mark)');
    }

    if (strpos($this->content, "\r") !== false) {
      $this->content = str_replace(array("\r\n", "\r"), "\n", $this->content);
      $this->addFixable('fix line endings to LF');
    }

    if (strpos($this->content, "\t") !== false) {
      $this->content = str_replace("\t", '  ', $this->content);
      $this->addFixable('convert tabs to spaces');
    }

    if (preg_match('/ $/m', $this->content)) {
      $this->content = preg_replace('/ +$/m', '', $this->content);
      $this->addFixable('remove trailing whitespace');
    }

    if (strlen($this->content) && substr($this->content, -1) != "\n") {
      $this->content .= "\n";
      $this->addFixable('add newline at end of file');
    }

    if (preg_match("/\n{2,}$/", $this->content)) {
      $this->content = rtrim($this->content, "\n") . "\n";
      $this->addFixable('remove multiple newlines at end of file');
    }
  }
}

class rex_coding_standards_fixer_php extends rex_coding_standards_fixer
{
  const
    MSG_INDENTATION = 'fix indentation',
    MSG_LOWERCASE_CONTROL_KEYWORD = 'replace control keywords by their lowercase variants',
    MSG_SPACE_BEHIND_CONTROL_KEYWORD = 'add space after control keywords ("if", "for" etc.)',
    MSG_SPACE_BEFORE_CONTROL_KEYWORD = 'add space before "else", "catch" and "use"',
    MSG_SPACES_AROUND_BINARY_OPERATOR = 'add spaces around binary operators ("=", "+", "&&" etc.)',
    MSG_NEWLINE_BEFORE_OPENING_BRACE = 'add newline before opening braces of classes/methods',
    MSG_VISIBILITY = 'add visibility to methods/properties',
    MSG_ATTRIBUTES_ORDER = 'reorder function/property attributes (final, abstract, static, visibility)';

  protected
    $checkNamingConventions,
    $removeClosingPhpTag,
    $tokens,
    $index,
    $previous,
    $previousNonWhitespace,
    $indentation = '',
    $class,
    $method,
    $function = 0,
    $isTernary = 0;

  public function __construct($content, $checkNamingConventions = true, $removeClosingPhpTag = true)
  {
    $this->checkNamingConventions = $checkNamingConventions;
    $this->removeClosingPhpTag = $removeClosingPhpTag;
    parent::__construct($content);
  }

  protected function fix()
  {
    parent::fix();

    $this->content = preg_replace('/<\?(?=\s)/', '<?php', $this->content, -1, $count);
    if ($count) {
      $this->addFixable('replace php short open tags "<?" by "<?php"');
    }

    if ($this->removeClosingPhpTag) {
      $this->content = preg_replace("/\n* *\?>$/", '', $this->content, -1, $count);
      if ($count) {
        $this->addFixable('remove php closing tag "?>" at end of file');
      }
    }

    $this->tokens = token_get_all($this->content);
    $this->content = '';
    $count = count($this->tokens);
    for ($this->index = 0; $this->index < $count; $this->index++) {
      $this->fixToken(new rex_php_token($this->tokens[$this->index]));
    }
    $this->content = preg_replace('/ +$/m', '', $this->content);
  }

  protected function addToken(rex_php_token $token)
  {
    $this->previous = $token;
    if (!in_array($token->type, array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true)) {
      $this->previousNonWhitespace = $token;
    }
    $this->content .= $token->text;
  }

  protected function previousToken($nonWhitespace = false)
  {
    return $nonWhitespace ? $this->previousNonWhitespace : $this->previous;
  }

  protected function nextToken()
  {
    $this->index++;
    if (isset($this->tokens[$this->index])) {
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
    switch ($token->type) {
      case T_WHITESPACE:
        if (($pos = strrpos($token->text, "\n")) !== false) {
          $this->indentation = substr($token->text, $pos + 1);
        }
        $this->addToken($token);
        break;

      case T_COMMENT:
        if (substr($token->text, -1) === "\n") {
          $token->text = substr($token->text, 0, -1);
          $this->addToken($token);
          $next = $this->nextToken();
          if ($next && $next->type === T_WHITESPACE) {
            $next->text = "\n" . $next->text;
            $this->fixToken($next);
          } else {
            $this->addToken(new rex_php_token(T_WHITESPACE, "\n"));
            $this->decrementTokenIndex();
          }
          break;
        }
        $this->addToken($token);
        break;

      case T_DOC_COMMENT:
        $doc = preg_replace('/^ *\*/m', $this->indentation . ' *', $token->text);
        $doc = preg_replace('/^( *\*) *@/m', '$1 @', $doc);
        preg_match_all('/^ *\* @param +(\S+) +(\$\w+)/m', $doc, $matches);
        if ($matches[1]) {
          $max = function ($max, $value) {
            return max($max, mb_strlen($value));
          };
          $maxHint = array_reduce($matches[1], $max);
          $maxVar  = array_reduce($matches[2], $max);
          $doc = preg_replace_callback('/^( *\* @param) +(\S+) +(\$\w+) *(.*)$/m', function ($match) use ($maxHint, $maxVar) {
            return $match[1] . ' ' . str_pad($match[2], $maxHint) . ' ' . rtrim(str_pad($match[3], $maxVar) . ' ' . $match[4]);
          }, $doc);
        }
        if ($doc !== $token->text) {
          $token->text = $doc;
          $this->addFixable('fix alignment in doc comments');
        }
        $this->addToken($token);
        break;

      case T_CONSTANT_ENCAPSED_STRING:
        if (preg_match('/^"([^${\'\\\\]*)"$/', $token->text, $match)) {
          $token->text = "'" . $match[1] . "'";
          $this->addFixable('replace double quotes around simple strings by single quotes');
        }
        $this->addToken($token);
        break;

      case T_STRING:
        if (in_array(strtolower($token->text), array('true', 'false', 'null'))) {
          $this->checkLowercase($token, 'replace boolean/null constants by their lowercase variants ("true", "false" and "null")');
        } elseif ($this->class && !$this->function && $token->text === $this->class) {
          $token->text = 'self';
          $this->addFixable('replace full class names by "self" in static/constructor calls');
        }
        $this->addToken($token);
        $this->checkNoSpaceBehind();
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
        if ($trim !== $token->text) {
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

      case T_ABSTRACT:
      case T_AS:
      case T_CALLABLE:
      case T_DECLARE:
      case T_DEFAULT:
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
      case T_GLOBAL:
      case T_GOTO:
      case T_IMPLEMENTS:
      case T_INSTANCEOF:
      case T_INSTEADOF:
      case T_ISSET:
      case T_LIST:
      case T_NAMESPACE:
      case T_NEW:
      case T_PRIVATE:
      case T_PUBLIC:
      case T_PROTECTED:
      case T_STATIC:
      case T_UNSET:
      case T_USE:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        break;

      case T_ARRAY:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkNoSpaceBehind();
        $this->skipWhitespace();
        $next = $this->nextToken();
        if ($next->type === rex_php_token::SIMPLE && $next->text === '(') {
          $this->addToken($next);
          $this->checkIndentation($this->indentation . '  ');
          break;
        }
        $this->decrementTokenIndex();
        break;

      case T_DO:
      case T_TRY:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkBraceInSameLine();
        break;

      case T_BREAK:
      case T_CONTINUE:
      case T_ECHO:
      case T_INCLUDE:
      case T_INCLUDE_ONCE:
      case T_PRINT:
      case T_REQUIRE:
      case T_REQUIRE_ONCE:
      case T_RETURN:
      case T_THROW:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_BEHIND_CONTROL_KEYWORD);
        $this->checkNoParanthesis(';');
        break;

      case T_CLONE:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_BEHIND_CONTROL_KEYWORD);
        $this->checkNoParanthesis(array(';', ','));
        break;

      case T_CATCH:
      case T_ELSEIF:
        $this->checkNoNewlineBefore();
      case T_IF:
      case T_FOR:
      case T_FOREACH:
      case T_WHILE:
      case T_SWITCH:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_BEHIND_CONTROL_KEYWORD);
        $this->searchFor('(', ')');
        $this->checkBraceInSameLine();
        break;

      case T_CASE:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_BEHIND_CONTROL_KEYWORD);
        $this->checkNoParanthesis(':');
        break;

      case T_ELSE:
        $next = $this->nextToken();
        if ($next->type === T_WHITESPACE) {
          $nextNext = $this->nextToken();
          if ($nextNext->type === T_IF) {
            $this->addFixable('replace "else if" by "elseif"');
            $this->fixToken(new rex_php_token(T_ELSEIF, 'elseif'));
            break;
          }
          $this->decrementTokenIndex();
        }
        $this->decrementTokenIndex();
        $this->checkNoNewlineBefore();
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkBraceInSameLine();
        break;

      case T_FUNCTION:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkSpaceBehind(self::MSG_SPACE_BEHIND_CONTROL_KEYWORD);
        $this->skipWhitespace();
        $next = $this->nextToken();
        if ($next->type === rex_php_token::SIMPLE && $next->text === '&') {
          $this->addToken($next);
          $this->skipWhitespace();
          $next = $this->nextToken();
        }
        if ($next->type === T_STRING) {
          if ($this->class && !$this->method) {
            if (preg_match('/(?:(?<finalabstract>final |abstract )|(?<static>static )|(?<visibility>private |protected |public ))*function $/', $this->content, $match)) {
              if (!isset($match['visibility'])) {
                $this->addNonFixable(self::MSG_VISIBILITY);
              } else {
                $content = (isset($match['finalabstract']) ? $match['finalabstract'] : '')
                  . (isset($match['static']) ? $match['static'] : '')
                  . $match['visibility'] . 'function ';
                if ($content !== $match[0]) {
                  $this->content = substr_replace($this->content, $content, -strlen($content));
                  $this->addFixable(self::MSG_ATTRIBUTES_ORDER);
                }
              }
            }
            $this->method = $next->text;
            if (!$this->ignoreName() && !preg_match('/^_{0,2}[a-z0-9]*$/i', $this->method)) {
              $msg = $this->method === $this->class ? 'use __construct() for constructor method' : 'use camelCase for method names, no underscores';
              $this->addNonFixable($msg);
            }
            $this->addToken($next);
            $this->checkNoSpaceBehind();
            if ($this->searchFor(array('{', ';')) === '{') {
              $this->checkNewlineBefore(self::MSG_NEWLINE_BEFORE_OPENING_BRACE);
              $this->searchFor('{', '}');
            }
            $this->method = null;
          } else {
            $this->function++;
            $this->addToken($next);
            $this->checkNoSpaceBehind();
            $this->searchFor('{');
            $this->checkNewlineBefore(self::MSG_NEWLINE_BEFORE_OPENING_BRACE);
            $this->searchFor('{', '}');
            $this->function--;
          }
          break;
        } elseif ($next->type === rex_php_token::SIMPLE && $next->text === '(') {
          $this->decrementTokenIndex();
          $this->searchFor('(', ')');
          $next = $this->nextToken();
          $nextNext = $this->nextToken();
          if ($next->type === T_USE) {
            $this->decrementTokenIndex();
            $nextNext = $next;
            $next = new rex_php_token(T_WHITESPACE, ' ');
            $this->addFixable(self::MSG_SPACE_BEFORE_CONTROL_KEYWORD);
          }
          if ($next->type === T_WHITESPACE && $nextNext->type === T_USE) {
            $this->fixToken($next);
            $this->fixToken($nextNext);
            $this->checkSpaceBehind(self::MSG_SPACE_BEHIND_CONTROL_KEYWORD);
            $this->searchFor('(', ')');
          } else {
            $this->decrementTokenIndex();
            $this->decrementTokenIndex();
          }
          $this->checkBraceInSameLine();
          $this->function++;
          $this->searchFor('{', '}');
          $this->function--;
          break;
        }
        $this->decrementTokenIndex();
        break;

      case T_VARIABLE:
        if ($this->class && !$this->method) {
          if (preg_match('/(?:(?<static> static)|(?<visibility> private| protected| public))*(?<space>\s*)$/', $this->content, $match)) {
            if (!isset($match['visibility'])) {
              $this->addNonFixable(self::MSG_VISIBILITY);
            } else {
              $content = (isset($match['static']) ? $match['static'] : '') . $match['visibility'] . $match['space'];
              if ($content !== $match[0]) {
                $this->content = substr_replace($this->content, $content, -strlen($content));
                $this->addFixable(self::MSG_ATTRIBUTES_ORDER);
              }
            }
            if ($this->isNewline($this->previousToken()) && preg_match('/\v( +)\V*\v\V*$/', $this->content, $match)) {
              $this->decrementTokenIndex();
              $this->decrementTokenIndex();
              $this->content = rtrim($this->content);
              $this->checkIndentation($match[1] . '  ', T_VARIABLE);
              break;
            }
          }
        }
        $this->addToken($token);
        break;

      case T_CLASS:
      case T_INTERFACE:
      case T_TRAIT:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->skipWhitespace();
        $next = $this->nextToken();
        if ($next->type === T_STRING) {
          if (!$this->ignoreName()) {
            if (strpos($next->text, 'rex_') !== 0 && $next->text !== 'rex') {
              $this->addNonFixable('use "rex_" prefix for class/interface names');
            }
            if (strtolower($next->text) !== $next->text) {
              $this->addNonFixable('use only lowercase and underscores in class/interface names');
            }
          }
          $this->addToken($next);
          $this->class = $next->text;
          $this->searchFor('{');
          $open = $this->nextToken();
          $next = $this->nextToken();
          if ($next->type === rex_php_token::SIMPLE && $next->text === '}') {
            $this->addToken($open);
            $this->addToken($next);
            return;
          } elseif ($next->type === T_WHITESPACE && preg_match('/^ +$/D', $next->text)) {
            $nextNext = $this->nextToken();
            if ($nextNext->type === rex_php_token::SIMPLE && $nextNext->text === '}') {
              $this->addToken($open);
              $this->addToken($next);
              $this->addToken($nextNext);
              return;
            }
            $this->decrementTokenIndex();
          }
          $this->decrementTokenIndex();
          $this->decrementTokenIndex();
          $this->checkNewlineBefore(self::MSG_NEWLINE_BEFORE_OPENING_BRACE);
          $this->searchFor('{', '}');
          $this->class = null;
          break;
        }
        $this->decrementTokenIndex();
        break;

      case T_CONST:
        $this->checkLowercase($token, self::MSG_LOWERCASE_CONTROL_KEYWORD);
        $this->addToken($token);
        $this->checkIndentation($this->indentation . '  ', T_STRING, function ($next) {
          if (strtoupper($next->text) !== $next->text) {
            return 'use only uppercase and underscores for constants';
          }
        });
        break;

      case T_VAR:
        $this->addToken($token);
        $this->addNonFixable('replace old "var $property;" syntax by using visibilities');
        break;

      case rex_php_token::SIMPLE:
        switch ($token->text) {
          case '?':
            $next = $this->nextToken();
            if ($next->type === rex_php_token::SIMPLE && $next->text === ':') {
              $token->text .= ':';
            } else {
              $this->decrementTokenIndex();
              $this->isTernary++;
            }
            $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            $this->addToken($token);
            $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            return;

          case ':':
            if ($this->isTernary) {
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
            $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            $this->addToken($token);
            $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
            return;

          case '+':
          case '-':
          case '&':
            $previous = $this->previousToken(true);
            if ($previous->type === rex_php_token::SIMPLE && in_array($previous->text, array(')', ']'))
              || in_array($previous->type, array(T_VARIABLE, T_DNUMBER, T_LNUMBER))) {
              $this->checkSpaceBefore(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
              $this->addToken($token);
              $this->checkSpaceBehind(self::MSG_SPACES_AROUND_BINARY_OPERATOR);
              return;
            }
            $this->addToken($token);
            return;

          case ',':
          case ';':
            $this->addToken($token);
            $this->checkSpaceBehind('add space after "," and ";"');
            return;

          case '{':
            $this->addToken($token);
            $this->checkNewlineBehind('add newline after opening brace');
            return;

          case '}':
            $previous = $this->previousToken(true);
            if ($previous->type !== rex_php_token::SIMPLE || $previous->text !== '{') {
              $this->checkNewlineBefore('add newline before closing braces', true);
            }
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

  private function ignoreName()
  {
    if (!$this->checkNamingConventions)
      return true;
    return preg_match('!// *@codingStandardsIgnoreName *\v\V*$!', $this->content);
  }

  private function skipWhitespace($skipNewlines = true)
  {
    $next = $this->nextToken();
    while (($next->type === T_WHITESPACE || $next->type === T_COMMENT) && ($skipNewlines || strpos($next->text, "\n") === false)) {
      $this->addToken($next);
      $next = $this->nextToken();
    }
    $this->decrementTokenIndex();
  }

  private function searchFor($start, $end = null, $inclusive = true)
  {
    if ($end === null) {
      $next = $this->nextToken();
      $search = (array) $start;
      while ($next->type !== rex_php_token::SIMPLE || !in_array($next->text, $search, true)) {
        $this->fixToken($next);
        $next = $this->nextToken();
      }
      $this->decrementTokenIndex();
      return $next->text;
    }
    $this->skipWhitespace();
    $count = $inclusive ? 0 : 1;
    do {
      $next = $this->nextToken();
      if ($next->type === rex_php_token::SIMPLE) {
        if ($next->text === $start) {
          $count++;
        } elseif ($next->text === $end) {
          $count--;
        }
      }
      if ($count || $inclusive) {
        $this->fixToken($next);
      }
    }
    while ($count);
    if (!$inclusive) {
      $this->decrementTokenIndex();
    }
  }

  private function checkNoParanthesis($end)
  {
    $this->skipWhitespace();
    $next = $this->nextToken();
    if ($next->type === rex_php_token::SIMPLE && $next->text === '(') {
      $content = $this->content;
      $this->content = '';
      $this->searchFor('(', ')', false);
      $this->nextToken();
      $next = $this->nextToken();
      if ($next->type === rex_php_token::SIMPLE && in_array($next->text, (array) $end, true)) {
        $this->addFixable('remove parantheses around argument of "echo", "include" etc.');
        $this->content = $content . $this->content;
      } else {
        $this->content = $content . '(' . $this->content . ')';
      }
    }
    $this->decrementTokenIndex();
  }

  private function checkBraceInSameLine()
  {
    $next = $this->nextToken();
    if ($next->type === rex_php_token::SIMPLE && $next->text === '{') {
      $this->addToken(new rex_php_token(T_WHITESPACE, ' '));
      $this->addFixable('add space before opening brace');
    } elseif ($this->isNewline($next)) {
      $nextNext = $this->nextToken();
      if ($nextNext->type === rex_php_token::SIMPLE && $nextNext->text === '{') {
        $next->text = ' ';
        $this->addToken($next);
        $this->addFixable('remove newline before opening brace of "if", "for", "while" etc.');
      } else {
        $this->decrementTokenIndex();
      }
    }
    $this->decrementTokenIndex();
  }

  private function checkIndentation($indentation, $type = null, $checkNameCallback = null)
  {
    $semicolon = new rex_php_token(rex_php_token::SIMPLE, ';');
    $comma = new rex_php_token(rex_php_token::SIMPLE, ',');
    $close = new rex_php_token(rex_php_token::SIMPLE, ')');
    $end = array('(' => ')', '{' => '}');
    do {
      $this->skipWhitespace(false);
      $next = $this->nextToken();
      if ($this->isNewline($next)) {
        $this->fixToken($next);
        $next = $this->nextToken();
        if ($next->type !== T_COMMENT) {
          $this->_checkIndentation($indentation, $next == $close);
        }
        if ($type && $next->type === $type) {
          if (is_callable($checkNameCallback) && !$this->ignoreName()) {
            if ($msg = call_user_func($checkNameCallback, $next)) {
              $this->addNonFixable($msg);
            }
          }
          $this->addToken($next);
          $next = $this->nextToken();
        }
      }
      while (!in_array($next, array($comma, $semicolon, $close))) {
        if ($next->type === rex_php_token::SIMPLE && in_array($next->text, array_keys($end))) {
          $this->decrementTokenIndex();
          $this->searchFor($next->text, $end[$next->text]);
        } else {
          $this->fixToken($next);
        }
        $next = $this->nextToken();
      }
      $this->fixToken($next);
    }
    while (!in_array($next, array($semicolon, $close)));
    if ($next == $close) {
      $this->_checkIndentation($indentation, true);
    }
  }

  private function _checkIndentation($indentation, $indentationBack = false)
  {
    if ($indentationBack) {
      $indentation = substr($indentation, 0, -2);
    }
    if (preg_match("/\n( *)\)?$/D", $this->content, $match) && $match[1] !== $indentation) {
      $this->content = preg_replace("/\n *(\)?)$/D", "\n" . $indentation . '$1', $this->content);
      $this->addFixable(self::MSG_INDENTATION);
    }
    $this->indentation = $indentation;
  }

  private function checkLowercase(rex_php_token $token, $msg)
  {
    $lowercaseText = strtolower($token->text);
    if ($lowercaseText !== $token->text) {
      $token->text = $lowercaseText;
      $this->addFixable($msg);
    }
  }

  private function checkUppercase(rex_php_token $token, $msg)
  {
    $uppercaseText = strtoupper($token->text);
    if ($uppercaseText !== $token->text) {
      $token->text = $uppercaseText;
      $this->addFixable($msg);
    }
  }

  private function checkSpaceBefore($msg)
  {
    if (!in_array($this->previousToken()->type, array(T_WHITESPACE, T_OPEN_TAG), true)) {
      $this->addToken(new rex_php_token(T_WHITESPACE, ' '));
      $this->addFixable($msg);
    }
  }

  private function checkSpaceBehind($msg)
  {
    $next = $this->nextToken();
    if ($next->type !== T_WHITESPACE && !($next->type === rex_php_token::SIMPLE && $next->text === ';')) {
      $this->addToken(new rex_php_token(T_WHITESPACE, ' '));
      $this->addFixable($msg);
    }
    $this->decrementTokenIndex();
  }

  private function checkNoSpaceBehind()
  {
    $next = $this->nextToken();
    if ($next->type === T_WHITESPACE) {
      $nextNext = $this->nextToken();
      if ($nextNext->type === rex_php_token::SIMPLE && $nextNext->text === '(') {
        $this->addFixable('remove space before opening parantheses of arrays and functions');
        $this->decrementTokenIndex();
        return;
      }
      $this->decrementTokenIndex();
    }
    $this->decrementTokenIndex();
  }

  private function checkNewlineBefore($msg, $indentationBack = false)
  {
    $previous = $this->previousToken();
    if (!$this->isNewline($previous)) {
      if ($indentationBack) {
        $this->indentation = substr($this->indentation, 0, -2);
      }
      $this->addToken(new rex_php_token(T_WHITESPACE, "\n" . $this->indentation));
      $this->addFixable($msg);
    }
  }

  private function checkNewlineBehind($msg)
  {
    $this->skipWhitespace(false);
    $next = $this->nextToken();
    $this->decrementTokenIndex();
    if (!$this->isNewline($next)) {
      $this->addFixable($msg);
      $this->fixToken(new rex_php_token(T_WHITESPACE, "\n" . $this->indentation . '  '));
    }
  }

  private function checkNoNewlineBefore()
  {
    $this->content = preg_replace("/\}\n *$/", '} ', $this->content, -1, $count);
    if ($count) {
      $this->addFixable('remove newline before "else", "elseif" and "catch"');
      return;
    }
    $this->checkSpaceBefore(self::MSG_SPACE_BEFORE_CONTROL_KEYWORD);
  }

  private function isNewline(rex_php_token $token)
  {
    return ($token->type === T_WHITESPACE || $token->type === T_COMMENT && strpos($token->text, '//') === 0) && strpos($token->text, "\n") !== false;
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
    if ($text) {
      $this->type = $token;
      $this->text = $text;
    } elseif (is_string($token)) {
      $this->type = self::SIMPLE;
      $this->text = $token;
    } else {
      $this->type = $token[0];
      $this->text = $token[1];
    }
  }
}

$hideProcess = in_array('--hide-process', $argv);
$textExtensions = array('css', 'gitignore', 'htaccess', 'html', 'js', 'json', 'lang', 'php', 'sql', 'textile', 'tpl', 'txt', 'yml');
$countFiles = 0;
$countFixable = 0;
$countNonFixable = 0;
if ($dir) {
  $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
} else {
  $file = realpath($file);
  $files = array($file => new SplFileInfo($file));
  $dir = dirname($file);
}
foreach ($files as $path => $file) {
  /* @var $file SplFileInfo */
  $subPath = str_replace($dir . DIRECTORY_SEPARATOR, '', $path);
  $fileExt = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
  if (!in_array($fileExt, $textExtensions)
    || strpos(DIRECTORY_SEPARATOR . $subPath, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false
    || strpos($path, DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'tinymce' . DIRECTORY_SEPARATOR) !== false
    || strpos($subPath, 'test_coding_standards') === 0) {
    continue;
  }

  if (!$hideProcess) {
    $checkString = $subPath;
    if (mb_strlen($checkString) > 60) {
      $checkString = mb_substr($checkString, 0, 20) . '...' . mb_substr($checkString, -37);
    }
    echo $checkString = 'check ' . $checkString . ' ...';
  }

  $countFiles++;
  if ($fileExt === 'php' || $fileExt === 'tpl') {
    $checkNamingConventions = strpos($path, DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'compat' . DIRECTORY_SEPARATOR) === false;
    $removeClosingPhpTag = $fileExt !== 'tpl';
    $fixer = new rex_coding_standards_fixer_php(file_get_contents($path), $checkNamingConventions, $removeClosingPhpTag);
  } else {
    $fixer = new rex_coding_standards_fixer(file_get_contents($path));
  }

  if (!$hideProcess) {
    echo str_repeat("\010 \010", mb_strlen($checkString));
  }

  if ($fixer->hasChanged()) {
    echo $subPath, ':', PHP_EOL;
    if ($fixable = $fixer->getFixable()) {
      echo '  > ', implode(PHP_EOL . '  > ', $fixable), PHP_EOL;
      $countFixable++;
    }
    if ($nonFixable = $fixer->getNonFixable()) {
      echo '  ! ', implode(PHP_EOL . '  ! ', $nonFixable), PHP_EOL;
      $countNonFixable++;
    }
    echo PHP_EOL;

    if ($fix) {
      file_put_contents($path, $fixer->getResult());
    }
  }
}

echo '-----------------------------------', PHP_EOL;
echo 'checked ', $countFiles, ' files', PHP_EOL;
if ($countFixable) {
  echo '', ($fix ? 'fixed' : 'found fixable'), ' problems in ', $countFixable, ' files', PHP_EOL;
}
if ($countNonFixable) {
  echo 'found non-fixable problems in ', $countNonFixable, ' files', PHP_EOL;
}

echo PHP_EOL;
if ($hasColorSupport) {
  echo ($countNonFixable + ($fix ? 0 : $countFixable)) ? "\033[1;37;41m" : "\033[1;30;42m";
}
echo 'FINISHED, ', !$countFixable && !$countNonFixable ? 'no problems' : 'found problems';
echo $hasColorSupport ? "\033[0m" : '';
echo PHP_EOL, PHP_EOL;

exit ($countNonFixable + ($fix ? 0 : $countFixable));
