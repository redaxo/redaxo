<?php

/**
 * Layout 
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 */

$error = '';

// Plugins mitinstallieren
$addonname = 'layout';

$plugins = array('base', 'agk_skin');

$pluginManager = new rex_pluginManager($addonname);

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
  $REX['ADDON']['installmsg'][$addonname] = $error;
else
  $REX['ADDON']['install'][$addonname] = true;