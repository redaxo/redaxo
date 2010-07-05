<?php

/**
 * Userinfo Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

// "agk_skin" Plugin mitinstallieren
//$addonname = 'be_style';
//$pluginname = 'agk_skin';
//
//$ADDONS    = rex_read_addons_folder();
//$PLUGINS   = array();
//foreach($ADDONS as $_addon)
//  $PLUGINS[$_addon] = rex_read_plugins_folder($_addon);
//
//$addonManager = new rex_pluginManager($PLUGINS, $addonname);
//$addonManager->install($pluginname);
//
//// plugin installieren
//if(($instErr = $addonManager->install('agk_skin')) !== true)
//{
//  $error = $instErr;
//}
//
//// plugin aktivieren
//if ($error == '' && ($actErr = $addonManager->activate('agk_skin')) !== true)
//{
//  $error = $actErr;
//}

if ($error != '')
  $REX['ADDON']['installmsg']['userinfo'] = $error;
else
  $REX['ADDON']['install']['userinfo'] = true;