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
  $REX['ADDON']['install']['be_style'] = 1;
  $REX['ADDON']['status']['be_style'] = 1;
  $REX['ADDON']['plugins']['be_style']['install']['base'] = 1;
  $REX['ADDON']['plugins']['be_style']['status']['base'] = 1;
  $REX['ADDON']['plugins']['be_style']['install']['agk_skin'] = 1;
  $REX['ADDON']['plugins']['be_style']['status']['agk_skin'] = 1;
  $packageOrder = array('be_style', array('be_style', 'base'), array('be_style', 'agk_skin'));
}
else
{
  $REX['ADDON'] = rex_core_config::get('package-config', array());
  $packageOrder = rex_core_config::get('package-order', array());
}

// in the first run, we register all folders for class- and fragment-loading,
// so it is transparent in which order the addons are included afterwards.
foreach($packageOrder as $addonName)
{
  if(!is_array($addonName))
  {
    $addonsFolder = rex_path::addon($addonName);

    // add addon path for fragment loading
    if(is_readable($addonsFolder .'fragments'))
    {
      rex_fragment::addDirectory($addonsFolder .'fragments'.DIRECTORY_SEPARATOR);
    }
    // add addon path for class-loading
    if(is_readable($addonsFolder .'lib'))
    {
      rex_autoload::addDirectory($addonsFolder .'lib'.DIRECTORY_SEPARATOR);
    }
    // add addon path for i18n
    if(is_readable($addonsFolder .'lang'))
    {
      rex_i18n::addDirectory($addonsFolder .'lang');
    }
    // load package infos
    rex_addonManager::loadPackage($addonName);
  }
  else
  {
    list($addonName, $pluginName) = $addonName;

    if(rex_ooAddon::isAvailable($addonName))
    {
      $pluginsFolder = rex_path::plugin($addonName, $pluginName);

      // add plugin path for fragment loading
      if(is_readable($pluginsFolder .'fragments'))
      {
        rex_fragment::addDirectory($pluginsFolder .'fragments'.DIRECTORY_SEPARATOR);
      }
      // add plugin path for class-loading
      if(is_readable($pluginsFolder .'lib'))
      {
        rex_autoload::addDirectory($pluginsFolder .'lib'.DIRECTORY_SEPARATOR);
      }
      // add plugin path for i18n
      if(is_readable($pluginsFolder .'lang'))
      {
        rex_i18n::addDirectory($pluginsFolder .'lang');
      }
      // load package infos
      rex_pluginManager::loadPackage($addonName, $pluginName);
    }
  }
}

// now we actually include the addons logic
foreach($packageOrder as $addonName)
{
  if(!is_array($addonName))
  {
    $addonsFolder = rex_path::addon($addonName);

    // include the addon itself
    if(is_readable($addonsFolder .'config.inc.php'))
    {
      require $addonsFolder .'config.inc.php';
    }
  }
  else
  {
    list($addonName, $pluginName) = $addonName;

    if(rex_ooAddon::isAvailable($addonName))
    {
      $pluginsFolder = rex_path::plugin($addonName, $pluginName);

      // transform the plugin into a regular addon and include it itself afterwards
      if(is_readable($pluginsFolder .'config.inc.php'))
      {
        rex_pluginManager::addon2plugin($addonName, $pluginName, $pluginsFolder .'config.inc.php');
      }
    }
  }
}

// ----- all addons configs included
rex_register_extension_point('ADDONS_INCLUDED');