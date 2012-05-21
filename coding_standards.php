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

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$textExtensions = array('php', 'js', 'yml', 'tpl', 'css', 'textile', 'sql', 'txt');
$countFixable = 0;
$countNonFixable = 0;
foreach ($iterator as $path => $file)
{
  /* @var $file SplFileInfo */
  if (!in_array($file->getExtension(), $textExtensions) || $path == __FILE__)
  {
    continue;
  }

  $content = file_get_contents($path);
  $fixable = array();
  $nonFixable = array();

  if (($encoding = mb_detect_encoding($content, 'UTF-8,ISO-8859-1,WINDOWS-1252')) != 'UTF-8')
  {
    $content = iconv($encoding, 'UTF-8', $content);
    $fixable[] = 'fix encoding to UTF-8';
  }
  elseif (strpos($content, "\xEF\xBB\xBF") === 0)
  {
    $content = substr($content, 3);
    $fixable[] = 'remove BOM (Byte Order Mark)';
  }

  if (strpos($content, "\r") !== false)
  {
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
    $fixable[] = 'fix line endings to LF';
  }

  if (strpos($content, "\t") !== false)
  {
    $content = str_replace("\t", '  ', $content);
    $fixable[] = 'convert tabs to spaces';
  }

  if (preg_match('/ $/m', $content))
  {
    $content = preg_replace('/ +$/m', '', $content);
    $fixable[] = 'remove trailing whitespace';
  }

  if (strlen($content) && substr($content, -1) != "\n")
  {
    $content .= "\n";
    $fixable[] = 'add newline at end of file';
  }

  if (preg_match("/\n{2,}$/", $content))
  {
    $content = rtrim($content, "\n") . "\n";
    $fixable[] = 'remove multiple newlines at end of file';
  }

  if ($file->getExtension() == 'php')
  {
    // TODO check php syntax
  }

  if (!empty($fixable) || !empty($nonFixable))
  {
    echo $iterator->getInnerIterator()->getSubPathName(), ':', PHP_EOL;
    if (!empty($fixable))
    {
      echo '  > ', implode(PHP_EOL . '  > ', $fixable), PHP_EOL;
      $countFixable++;
    }
    if (!empty($nonFixable))
    {
      echo '  ! ', implode(PHP_EOL . '  ! ', $nonFixable), PHP_EOL;
      $countNonFixable++;
    }
    echo PHP_EOL;

    if ($fix)
    {
      file_put_contents($path, $content);
    }
  }
}

echo 'FINISHED';
if ($countFixable)
{
  echo ', ', ($fix ? 'fixed' : 'found fixable'), ' problems in ', $countFixable, ' files';
}
if ($countNonFixable)
{
  echo ', found non-fixable problems in ', $countNonFixable, ' files';
}
if(!$countFixable && !$countNonFixable)
{
  echo ', no problems';
}
echo '.', PHP_EOL, PHP_EOL;

exit ($countNonFixable + ($fix ? 0 : $countFixable));
