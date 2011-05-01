<?php

/**
 * Packages loading
 * @package redaxo5
 * @version svn:$Id$
 */

// ----------------- addons
unset($REX['ADDON']);
$REX['ADDON'] = array();

if($REX['SETUP'])
{
  /*$REX['ADDON']['install']['be_style'] = 1;
  $REX['ADDON']['status']['be_style'] = 1;
  $REX['ADDON']['plugins']['be_style']['install']['base'] = 1;
  $REX['ADDON']['plugins']['be_style']['status']['base'] = 1;
  $REX['ADDON']['plugins']['be_style']['install']['agk_skin'] = 1;
  $REX['ADDON']['plugins']['be_style']['status']['agk_skin'] = 1;*/
  //$packageOrder = array('be_style', array('be_style', 'base'), array('be_style', 'agk_skin'));
  $packageOrder = array();
}
else
{
  rex_addon::initialize();
  $packageOrder = rex_core_config::get('package-order', array());
}

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach($packageOrder as $packageRepresentation)
{
  $package = rex_package::get($packageRepresentation);
  $folder = $package->getBasePath();

  // add package path for fragment loading
  if(is_readable($folder .'fragments'))
  {
    rex_fragment::addDirectory($folder .'fragments'.DIRECTORY_SEPARATOR);
  }
  // add addon path for class-loading
  if(is_readable($folder .'lib'))
  {
    rex_autoload::addDirectory($folder .'lib'.DIRECTORY_SEPARATOR);
  }
  // add addon path for i18n
  if(is_readable($folder .'lang'))
  {
    rex_i18n::addDirectory($folder .'lang');
  }
  // load package infos
  rex_packageManager::loadPackageInfos($package);
}

// now we actually include the addons logic
foreach($packageOrder as $packageRepresentation)
{
  $package = rex_package::get($packageRepresentation);
  $folder = $package->getBasePath();

  // include the addon itself
  if(is_readable($folder .'config.inc.php'))
  {
    $package->includeFile('config.inc.php');
  }
}

// ----- all addons configs included
rex_extension::registerPoint('ADDONS_INCLUDED');