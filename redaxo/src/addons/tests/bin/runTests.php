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
  echo "error: start this script from a redaxo projects' root folder";
  return 2;
}

// ---- bootstrap REX

$REX = array();
$REX['REDAXO'] = true;
$REX['HTDOCS_PATH'] = '../';
$REX['BACKEND_FOLDER'] = 'redaxo';

// bootstrap core
include 'src/core/master.inc.php';

// TODO DO INTIAL SETUP!
// disable setup so autoloading of addons classes will work
$wasSetup = rex::getProperty('setup');
rex::setProperty('setup', false);

// bootstrap addons
include_once rex_path::core('packages.inc.php');

// run the tests
$tests = rex_dir::recursiveIterator(dirname(__FILE__).'/../lib/tests', rex_dir_recursive_iterator::LEAVES_ONLY)->ignoreSystemStuff();

$runner = new rex_test_runner();
$runner->setUp();
$result = $runner->run($tests);

echo $result;

rex::setProperty('setup', $wasSetup);

exit(strpos($result, 'FAILURES!') === false ? 0 : 99);
