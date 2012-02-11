<?php

/**
 * Backendstyle Addon
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

$error = '';

/*// Plugins mitinstallieren
$addonname = 'be_style';
$plugins = array('base', 'agk_skin');

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
