#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli')
{
  echo 'error: this script may only be run from CLI', PHP_EOL;
  exit(1);
}

echo PHP_EOL, '== REDAXO CODING STANDARDS CHECK ==', PHP_EOL, PHP_EOL;

if (!isset($argv[1]))
{
  echo 'ERROR: Missing mode argument! Possible modes are "check" and "fix".', PHP_EOL, PHP_EOL;
  exit(1);
}

$mode = $argv[1];
if (!in_array($mode, array('fix', 'check')))
{
  echo 'ERROR: Wrong mode argument "', $mode, '"! Possible modes are "check" and "fix".', PHP_EOL, PHP_EOL;
  exit(1);
}
$fix = $mode == 'fix';

$dir = __DIR__;
if (isset($argv[2]))
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
    if (!isset($argv[3]))
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

    $this->fixable = array_unique($this->fixable);
    $this->nonFixable = array_unique($this->nonFixable);
  }

  public function hasChanged()
  {
    return !empty($this->fixable) || !empty($this->nonFixable);
  }

  public function getFixable()
  {
    return $this->fixable;
  }

  public function getNonFixable()
  {
    return $this->nonFixable;
  }

  public function getResult()
  {
    return $this->content;
  }

  protected function addFixable($fixable)
  {
    $this->fixable[] = $fixable;
  }

  protected function addNonFixable($nonFixable)
  {
    $this->nonFixable[] = $nonFixable;
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
  protected
    $tokens,
    $index,
    $previous,
    $indentation = '';

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
      case T_IF:
      case T_FOR:
      case T_FOREACH:
      case T_WHILE:
      case T_SWITCH:
      case T_CASE:
        $this->addToken($token);
        $this->checkSpaceBehind();
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
        $this->addToken($token);
        break;

      case T_ELSEIF:
      case T_CATCH:
        $this->checkNewlineBefore();
        $this->addToken($token);
        $this->checkSpaceBehind();
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

      case rex_php_token::SIMPLE:
        if ($token->text === '{')
        {
          $next = $this->nextToken();
          if ($next->type === rex_php_token::SIMPLE && $next->text === '}')
          {
            $this->addToken($token);
            $this->addToken($next);
            break;
          }
          elseif ($next->type === T_WHITESPACE && preg_match('/^ +$/D', $next->text))
          {
            $nextNext = $this->nextToken();
            if ($nextNext->type === rex_php_token::SIMPLE && $nextNext->text === '}')
            {
              $this->addToken($token);
              $this->addToken($next);
              $this->addToken($nextNext);
              break;
            }
            $this->decrementTokenIndex();
          }
          $this->checkNewlineBefore();
          $this->addToken($token);
          $this->decrementTokenIndex();
          $this->skipSpacesAndComments();
          $next = $this->nextToken();
          $this->decrementTokenIndex();
          if (!$this->isNewline($next))
          {
            $this->addFixable('add newline after opening brace');
            $this->fixToken(new rex_php_token(T_WHITESPACE, "\n" . $this->indentation . '  '));
          }
          break;
        }
        elseif ($token->text === '}')
        {
          $this->checkNewlineBefore(true);
        }
        $this->addToken($token);
        break;

      default:
        $this->addToken($token);
    }
  }

  private function skipSpacesAndComments()
  {
    $next = $this->nextToken();
    while (($next->type === T_WHITESPACE || $next->type === T_COMMENT) && strpos($next->text, "\n") === false)
    {
      $this->addToken($next);
      $next = $this->nextToken();
    }
    $this->decrementTokenIndex();
  }

  private function checkSpaceBehind()
  {
    $next = $this->nextToken();
    if ($next->type !== T_WHITESPACE)
    {
      $this->addToken(new rex_php_token(T_WHITESPACE, ' '));
      $this->addFixable('add space after control keywords ("if", "for" etc.)');
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

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$textExtensions = array('php', 'js', 'yml', 'tpl', 'css', 'textile', 'sql', 'txt');
$countFiles = 0;
$countFixable = 0;
$countNonFixable = 0;
foreach ($iterator as $path => $file)
{
  /* @var $file SplFileInfo */
  $subPath = $iterator->getInnerIterator()->getSubPathName();
  if ($path == __FILE__
    || !in_array($file->getExtension(), $textExtensions)
    || strpos($subPath, '/vendor/') !== false)
  {
    continue;
  }

  $countFiles++;
  if($file->getExtension() == 'php')
  {
    $fixer = new rex_coding_standards_fixer_php(file_get_contents($path));
  }
  else
  {
    $fixer = new rex_coding_standards_fixer(file_get_contents($path));
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

echo 'FINISHED:', PHP_EOL;
echo ' - checked ', $countFiles, ' files', PHP_EOL;
if ($countFixable)
{
  echo ' - ', ($fix ? 'fixed' : 'found fixable'), ' problems in ', $countFixable, ' files', PHP_EOL;
}
if ($countNonFixable)
{
  echo ' - found non-fixable problems in ', $countNonFixable, ' files', PHP_EOL;
}
if(!$countFixable && !$countNonFixable)
{
  echo ' - no problems', PHP_EOL;
}
echo PHP_EOL;

exit ($countNonFixable + ($fix ? 0 : $countFixable));
