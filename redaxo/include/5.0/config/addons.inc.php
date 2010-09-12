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

foreach(OOAddon::getAvailableAddons() as $addonName)
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
  if(isset($I18N) && is_readable($addonsFolder .'lang'))
  {
    $I18N->appendFile($addonsFolder .'lang');
  }
  // include the addon itself
  if(file_exists($addonsFolder. 'config.inc.php'))
  {
    require $addonsFolder. 'config.inc.php';
  }
  
  foreach(OOPlugin::getAvailablePlugins($addonName) as $pluginName)
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
    if(isset($I18N) && is_readable($pluginsFolder .'lang'))
    {
      $I18N->appendFile($pluginsFolder .'lang');
    }
    // transform the plugin into a regular addon and include it itself afterwards 
    if(file_exists($pluginsFolder. 'config.inc.php'))
    {
      rex_pluginManager::addon2plugin($addonName, $pluginName, $pluginsFolder. 'config.inc.php');
    }
  }
}

// ----- all addons configs included
rex_register_extension_point('ADDONS_INCLUDED');

// ----- Init REX-Vars 
//require_once $REX['SRC_PATH'].'/core/classes/class.rex_var.inc.php';
foreach($REX['VARIABLES'] as $key => $value)
{
//  require_once ($REX['SRC_PATH'].'/core/classes/variables/class.'.$value.'.inc.php');
  $REX['VARIABLES'][$key] = new $value;
}