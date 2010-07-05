<?php

/**
 * Backendstyle Addon
 * 
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

// "agk_skin" Plugin mitinstallieren
$addonname = 'be_style';
$plugins = array('agk_skin');

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
  $REX['ADDON']['installmsg']['be_style'] = $error;
else
  $REX['ADDON']['install']['be_style'] = true;