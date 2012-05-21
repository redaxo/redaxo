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

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$textExtensions = array('php', 'js', 'yml', 'tpl', 'css', 'textile', 'sql', 'txt');
$countFiles = 0;
$countFixable = 0;
$countNonFixable = 0;
foreach ($iterator as $path => $file)
{
  /* @var $file SplFileInfo */
  if (!in_array($file->getExtension(), $textExtensions) || $path == __FILE__)
  {
    continue;
  }

  $countFiles++;
  $content = file_get_contents($path);
  $fixable = array();
  $nonFixable = array();

  if (($encoding = mb_detect_encoding($content, 'UTF-8,ISO-8859-1,WINDOWS-1252')) != 'UTF-8')
  {
    if ($encoding === false)
    {
      $encoding = mb_detect_encoding($content);
    }
    if ($encoding !== false)
    {
      $content = iconv($encoding, 'UTF-8', $content);
      $fixable[] = 'fix encoding from ' . $encoding . ' to UTF-8';
    }
    else
    {
      $nonFixable[] = 'couldn\'t detect encoding, change it to UTF-8';
    }
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
