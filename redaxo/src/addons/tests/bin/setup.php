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
require 'src/core/master.inc.php';

// run setup, if instance not already prepared
if(rex::isSetup())
{
  // read initial config
  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);

  // init db
  echo rex_setup::checkDb($config, false);
  echo rex_setup_importer::prepareEmptyDb();
  echo rex_setup_importer::verifyDbSchema();

  // install tests addon
  $manager = rex_package_manager::factory(rex_package::get('tests'));
  $manager->install();
  $manager->activate();

  $config['setup'] = false;
  if(rex_file::putConfig($configFile, $config))
  {
    echo "instance setup successfull";
    exit (0);
  }
  echo "instance setup failure";
  exit (1);
}
else
{
  echo "instance setup not necessary";
  exit (0);
}
