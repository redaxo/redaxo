#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli')
{
  echo 'error: this script may only be run from CLI', PHP_EOL;
  return 1;
}

echo PHP_EOL, '== REDAXO CODING STANDARDS FIXER ==', PHP_EOL, PHP_EOL;

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
$textExtensions = array('php', 'js', 'yml', 'tpl', 'css', 'textile', 'sql');
$countFixed = 0;
foreach ($iterator as $path => $file)
{
  /* @var $file SplFileInfo */
  if (!in_array($file->getExtension(), $textExtensions) || $path == __FILE__)
  {
    continue;
  }

  $content = file_get_contents($path);
  $actions = array();

  if (($encoding = mb_detect_encoding($content, 'UTF-8,ISO-8859-1,WINDOWS-1252')) != 'UTF-8')
  {
    $content = iconv($encoding, 'UTF-8', $content);
    $actions[] = 'fixed encoding to UTF-8';
  }
  elseif (strpos($content, "\xEF\xBB\xBF") === 0)
  {
    $content = substr($content, 3);
    $actions[] = 'removed BOM';
  }

  if (strpos($content, "\r") !== false)
  {
    $content = str_replace(array("\r\n", "\r"), "\n", $content);
    $actions[] = 'fixed line endings to LF';
  }

  if (strpos($content, "\t") !== false)
  {
    $content = str_replace("\t", '  ', $content);
    $actions[] = 'converted tabs to spaces';
  }

  if (preg_match('/ $/m', $content))
  {
    $content = preg_replace('/ +$/m', '', $content);
    $actions[] = 'removed trailing whitespace';
  }

  if (strlen($content) && substr($content, -1) != "\n")
  {
    $content .= "\n";
    $actions[] = 'added newline at end of file';
  }

  if (preg_match("/\n{2,}$/", $content))
  {
    $content = rtrim($content, "\n") . "\n";
    $actions[] = 'removed multiple newlines at end of file';
  }

  if ($file->getExtension() == 'php')
  {
    // TODO check php syntax
  }

  if (!empty($actions))
  {
    echo $iterator->getInnerIterator()->getSubPathName(), ':', PHP_EOL;
    echo '  > ', implode(PHP_EOL . '  > ', $actions), PHP_EOL;
    echo PHP_EOL;

    $countFixed++;

    file_put_contents($path, $content);
  }
}

echo 'FINISHED, fixed ', $countFixed, ' files.', PHP_EOL, PHP_EOL;
