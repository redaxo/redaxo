<?php

/**
 * Packages loading
 * @package redaxo5
 */

rex_addon::initialize(!rex::isSetup());

if (rex::isSetup() || rex::isSafeMode()) {
  $packageOrder = array_keys(rex_package::getSetupPackages());
} else {
  $packageOrder = rex::getConfig('package-order', array());
}

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach ($packageOrder as $packageId) {
  $package = rex_package::get($packageId);
  $folder = $package->getBasePath();

  // add addon path for i18n
  if (is_readable($folder . 'lang')) {
    rex_i18n::addDirectory($folder . 'lang');
  }
  // add package path for fragment loading
  if (is_readable($folder . 'fragments')) {
    rex_fragment::addDirectory($folder . 'fragments' . DIRECTORY_SEPARATOR);
  }
  // add addon path for class-loading
  if (is_readable($folder . 'lib')) {
    rex_autoload::addDirectory($folder . 'lib');
  }
  if (is_readable($folder . 'vendor')) {
    rex_autoload::addDirectory($folder . 'vendor');
  }
  $autoload = $package->getProperty('autoload');
  if (is_array($autoload) && isset($autoload['classes']) && is_array($autoload['classes'])) {
    foreach ($autoload['classes'] as $dir) {
      $dir = $package->getBasePath($dir);
      if (is_readable($dir)) {
        rex_autoload::addDirectory($dir);
      }
    }
  }
}

// now we actually include the addons logic
foreach ($packageOrder as $packageId) {
  $package = rex_package::get($packageId);
  $folder = $package->getBasePath();

  // include the addon itself
  if (is_readable($folder . 'config.inc.php')) {
    rex_package_manager::includeFile($package, rex_package_manager::CONFIG_FILE);
  }
}

// ----- all addons configs included
rex_extension::registerPoint('ADDONS_INCLUDED');
