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

require $REX['SRC_PATH']. '/config/plugins.inc.php';

/**
 * @var $addonName rex_sql
 */
foreach(OOAddon::getAvailableAddons() as $addonName)
{
  $addonsFolder = rex_addons_folder($addonName);
  $addonConfig = $addonsFolder. 'config.inc.php';
  if(file_exists($addonConfig))
  {
    require $addonConfig;
  }
  if(is_readable($addonsFolder .'fragments'))
  {
    rex_fragment::addDirectory($addonsFolder .'fragments/');
  }
  if(is_readable($addonsFolder .'lib'))
  {
    rex_autoload::getInstance()->addDirectory($addonsFolder .'lib/');
  }
  
  foreach(OOPlugin::getAvailablePlugins($addonName) as $pluginName)
  {
    $pluginConfig = rex_plugins_folder($addonName, $pluginName). 'config.inc.php';
    if(file_exists($pluginConfig))
    {
      rex_pluginManager::addon2plugin($addonName, $pluginName, $pluginConfig);
    }
  }
}

// ----- all addons configs included
rex_register_extension_point('ADDONS_INCLUDED');