<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

/*// Plugins mitinstallieren
$addonname = 'structure';
$plugins = array('content', 'linkmap');

$pluginManager = new rex_plugin_manager($addonname);

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
}*/

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);