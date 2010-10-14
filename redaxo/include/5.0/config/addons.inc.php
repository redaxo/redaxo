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
$REX['ADDON']['install']['be_dashboard'] = '1';
$REX['ADDON']['status']['be_dashboard'] = '0';

$REX['ADDON']['install']['be_search'] = '1';
$REX['ADDON']['status']['be_search'] = '1';

$REX['ADDON']['install']['be_style'] = '1';
$REX['ADDON']['status']['be_style'] = '1';

$REX['ADDON']['install']['community'] = '0';
$REX['ADDON']['status']['community'] = '0';

$REX['ADDON']['install']['cronjob'] = '1';
$REX['ADDON']['status']['cronjob'] = '1';

$REX['ADDON']['install']['editme'] = '0';
$REX['ADDON']['status']['editme'] = '0';

$REX['ADDON']['install']['image_manager'] = '1';
$REX['ADDON']['status']['image_manager'] = '1';

$REX['ADDON']['install']['image_resize'] = '0';
$REX['ADDON']['status']['image_resize'] = '0';

$REX['ADDON']['install']['import_export'] = '1';
$REX['ADDON']['status']['import_export'] = '1';

$REX['ADDON']['install']['mediapool'] = '1';
$REX['ADDON']['status']['mediapool'] = '1';

$REX['ADDON']['install']['metainfo'] = '1';
$REX['ADDON']['status']['metainfo'] = '1';

$REX['ADDON']['install']['modules'] = '1';
$REX['ADDON']['status']['modules'] = '1';

$REX['ADDON']['install']['phpmailer'] = '0';
$REX['ADDON']['status']['phpmailer'] = '0';

$REX['ADDON']['install']['structure'] = '1';
$REX['ADDON']['status']['structure'] = '1';

$REX['ADDON']['install']['templates'] = '1';
$REX['ADDON']['status']['templates'] = '1';

$REX['ADDON']['install']['textile'] = '0';
$REX['ADDON']['status']['textile'] = '0';

$REX['ADDON']['install']['tinymce'] = '0';
$REX['ADDON']['status']['tinymce'] = '0';

$REX['ADDON']['install']['url_rewrite'] = '0';
$REX['ADDON']['status']['url_rewrite'] = '0';

$REX['ADDON']['install']['users'] = '1';
$REX['ADDON']['status']['users'] = '1';

$REX['ADDON']['install']['version'] = '0';
$REX['ADDON']['status']['version'] = '0';

$REX['ADDON']['install']['xform'] = '0';
$REX['ADDON']['status']['xform'] = '0';
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