<?php

/**
 * Addonlist
 * @package redaxo4
 * @version svn:$Id$
 */

// ----------------- addons
unset($REX['ADDON']);
$REX['ADDON'] = array();

// ----------------- DONT EDIT BELOW THIS
// --- DYN
// --- /DYN
// ----------------- /DONT EDIT BELOW THIS

require $REX['INCLUDE_PATH']. '/config/plugins.inc.php';

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach(rex_ooAddon::getAvailableAddons() as $addonName)
{
  $addonsFolder = rex_addons_folder($addonName);

  // add addon path for fragment loading
  if(is_readable($addonsFolder .'fragments'))
  {
    rex_fragment::addDirectory($addonsFolder .'fragments/');
  }
  // add addon path for class-loading
  if(is_readable($addonsFolder .'lib'))
  {
    rex_autoload::getInstance()->addDirectory($addonsFolder .'lib/');
  }
  // add addon path for i18n
  if(isset($REX['I18N']) && is_readable($addonsFolder .'lang'))
  {
    $REX['I18N']->appendFile($addonsFolder .'lang');
  }
  // load package infos
  rex_addonManager::loadPackage($addonName);

  foreach(rex_ooPlugin::getAvailablePlugins($addonName) as $pluginName)
  {
    $pluginsFolder = rex_plugins_folder($addonName, $pluginName);

    // add plugin path for fragment loading
    if(is_readable($pluginsFolder .'fragments'))
    {
      rex_fragment::addDirectory($pluginsFolder .'fragments/');
    }
    // add plugin path for class-loading
    if(is_readable($pluginsFolder .'lib'))
    {
      rex_autoload::getInstance()->addDirectory($pluginsFolder .'lib/');
    }
    // add plugin path for i18n
    if(isset($REX['I18N']) && is_readable($pluginsFolder .'lang'))
    {
      $REX['I18N']->appendFile($pluginsFolder .'lang');
    }
    // load package infos
    rex_pluginManager::loadPackage($addonName, $pluginName);
  }
}

// now we actually include the addons logic
foreach(rex_ooAddon::getAvailableAddons() as $addonName)
{
  $addonsFolder = rex_addons_folder($addonName);

  // include the addon itself
  if(is_readable($addonsFolder. 'config.inc.php'))
  {
    require $addonsFolder. 'config.inc.php';
  }

  foreach(rex_ooPlugin::getAvailablePlugins($addonName) as $pluginName)
  {
    $pluginsFolder = rex_plugins_folder($addonName, $pluginName);

    // transform the plugin into a regular addon and include it itself afterwards
    if(is_readable($pluginsFolder. 'config.inc.php'))
    {
      rex_pluginManager::addon2plugin($addonName, $pluginName, $pluginsFolder. 'config.inc.php');
    }
  }
}

// ----- all addons configs included
rex_register_extension_point('ADDONS_INCLUDED');

// ----- Init REX-Vars
//require_once $REX['INCLUDE_PATH'].'/core/classes/class.rex_var.inc.php';
foreach($REX['VARIABLES'] as $key => $value)
{
//  require_once ($REX['INCLUDE_PATH'].'/core/classes/variables/class.'.$value.'.inc.php');
  $REX['VARIABLES'][$key] = new $value;
}