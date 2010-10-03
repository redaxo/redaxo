<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

// Plugins mitinstallieren
$addonname = 'structure';
$plugins = array('content', 'linkmap');

$ADDONS    = rex_read_addons_folder();
$PLUGINS   = array();
foreach($ADDONS as $_addon)
  $PLUGINS[$_addon] = rex_read_plugins_folder($_addon);

$pluginManager = new rex_pluginManager($PLUGINS, $addonname);

foreach($plugins as $pluginname)
{
  // plugin installieren
  if(($instErr = $pluginManager->install($pluginname)) !== true)
  {
    $error = $instErr;
  }
  
  // plugin aktivieren
  if ($error == '' && ($actErr = $pluginManager->activate($pluginname)) !== true)
  {
    $error = $actErr;
  }
  
  if($error != '')
  {
    break;
  }
}

if ($error != '')
  $REX['ADDON']['installmsg']['structure'] = $error;
else
  $REX['ADDON']['install']['structure'] = true;