<?php

/**
 * Packages loading
 * @package redaxo5
 */

rex_addon::initialize(!rex::isSetup());

if(rex::isSetup() || rex::isSafeMode())
{
  $packageOrder = array_keys(rex_package::getSetupPackages());
}
else
{
  $packageOrder = rex::getConfig('package-order', array());
}

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach($packageOrder as $packageId)
{
  $package = rex_package::get($packageId);
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
}

// now we actually include the addons logic
foreach($packageOrder as $packageId)
{
  $package = rex_package::get($packageId);
  $folder = $package->getBasePath();

  // include the addon itself
  if(is_readable($folder .'config.inc.php'))
  {
    rex_package_manager::includeFile($package, rex_package_manager::CONFIG_FILE);
  }
}

// ----- all addons configs included
rex_extension::registerPoint('ADDONS_INCLUDED');
