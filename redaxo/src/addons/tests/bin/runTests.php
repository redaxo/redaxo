#!/usr/bin/php
<?php

if (PHP_SAPI !== 'cli')
{
  echo "error: this script may only be run from CLI";
  return 1;
}

// bring the file into context, no matter from which dir it was executed
$path = explode(DIRECTORY_SEPARATOR, __DIR__);
do {
  $part = array_pop($path);
}
while($part !== null && $part != 'redaxo');

if(!chdir(implode(DIRECTORY_SEPARATOR, $path). '/redaxo'))
{
  echo "error: start this script within a redaxo projects folder";
  return 2;
}

// ---- bootstrap REX

$REX = array();
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';

// bootstrap core
require 'src/core/master.inc.php';

// bootstrap addons
include_once rex_path::core('packages.inc.php');

$runner = new rex_test_runner();
$runner->setUp();
$result = $runner->run(rex_test_locator::defaultLocator(), array('colors' => true));

echo $result;

exit(strpos($result, 'FAILURES!') === false ? 0 : 99);
