<?php
/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

rex_title($REX['I18N']->msg('addon'), '');

// -------------- RequestVars
$addonname  = rex_request('addonname', 'string');
$pluginname = rex_request('pluginname', 'string');
$subpage    = rex_request('subpage', 'string');
$info       = stripslashes(rex_request('info', 'string'));
$warning = '';


// -------------- READ CONFIG
$ADDONS    = rex_ooAddon::getRegisteredAddons();
$PLUGINS   = array();
foreach($ADDONS as $_addon)
{
  $PLUGINS[$_addon] = rex_ooPlugin::getRegisteredPlugins($_addon);
}

// -------------- CHECK IF CONFIG FILES ARE UP2DATE
if ($subpage == '')
{
  // Vergleiche Addons aus dem Verzeichnis addons/ mit den Eintraegen in config/addons.inc.php
  // Wenn ein Addon in der Datei fehlt oder nicht mehr vorhanden ist, aendere den Dateiinhalt.
  $addonsFilesys = rex_read_addons_folder();
  if (count(array_diff($ADDONS, $addonsFilesys)) > 0 ||
      count(array_diff($addonsFilesys, $ADDONS)) > 0)
  {
    if (($state = rex_generateAddons($addonsFilesys)) !== true)
    {
      $warning .= $state;
    }
  }

  // read all plugins which are present in the folders
  $pluginsFilesys = array();
  foreach($addonsFilesys as $addon)
  {
    $pluginsFilesys[$addon] = rex_read_plugins_folder($addon);
  }

  // Vergleiche plugins aus dem Verzeichnis plugins/ mit den Eintraegen in include/plugins.inc.php
  // Wenn ein plugin in der Datei fehlt oder nicht mehr vorhanden ist, aendere den Dateiinhalt.
  foreach($addonsFilesys as $addon)
  {
    if (!isset($PLUGINS[$addon]) ||
        count(array_diff($PLUGINS[$addon], $pluginsFilesys[$addon])) > 0 ||
        count(array_diff($pluginsFilesys[$addon], $PLUGINS[$addon])) > 0)
    {
      if (($state = rex_generatePlugins($pluginsFilesys)) !== true)
      {
        $warning .= $state;
      }
      break;
    }
  }

}

// -------------- Sanity checks
$addonname  = in_array($addonname, rex_ooAddon::getRegisteredAddons()) ? $addonname : '';
if($addonname != '')
  $pluginname = in_array($pluginname, rex_ooPlugin::getRegisteredPlugins($addonname)) ? $pluginname : '';
else
  $pluginname = '';

if($pluginname != '')
{
  $addonManager = new rex_pluginManager($addonname);
}
else
{
  $addonManager = new rex_addonManager();
}

// ----------------- HELPPAGE
if ($subpage == 'help' && $addonname != '')
{
  if($pluginname != '')
  {
    $helpfile    = rex_path::plugin($addonname, $pluginname, 'help.inc.php');
    $version     = rex_ooPlugin::getVersion($addonname, $pluginname);
    $author      = rex_ooPlugin::getAuthor($addonname, $pluginname);
    $supportPage = rex_ooPlugin::getSupportPage($addonname, $pluginname);
    $addonname   = $addonname .' / '. $pluginname;
  }
  else
  {
    $helpfile    = rex_path::addon($addonname, 'help.inc.php');
    $version     = rex_ooAddon::getVersion($addonname);
    $author      = rex_ooAddon::getAuthor($addonname);
    $supportPage = rex_ooAddon::getSupportPage($addonname);
  }

  $credits = '';
  $credits .= $REX['I18N']->msg("credits_name") .': <span>'. htmlspecialchars($addonname) .'</span><br />';
  if($version) $credits .= $REX['I18N']->msg("credits_version") .': <span>'. $version .'</span><br />';
  if($author) $credits .= $REX['I18N']->msg("credits_author") .': <span>'. htmlspecialchars($author) .'</span><br />';
  if($supportPage) $credits .= $REX['I18N']->msg("credits_supportpage") .': <span><a href="http://'.$supportPage.'" onclick="window.open(this.href); return false;">'. $supportPage .'</a></span><br />';

  echo '<div class="rex-area">
  			<h3 class="rex-hl2">'.$REX['I18N']->msg("addon_help").' '.$addonname.'</h3>
	  		<div class="rex-area-content">';
  if (!is_file($helpfile))
  {
    echo '<p>'. $REX['I18N']->msg("addon_no_help_file") .'</p>';
  }
  else
  {
    include $helpfile;
  }
  echo '<br />
        <p id="rex-addon-credits">'. $credits .'</p>
        </div>
  			<div class="rex-area-footer">
  				<p><a href="javascript:history.back();">'.$REX['I18N']->msg("addon_back").'</a></p>
  			</div>
  		</div>';
}

// ----------------- FUNCTIONS
if ($addonname != '')
{
  $install    = rex_get('install', 'int', -1);
  $activate   = rex_get('activate', 'int', -1);
  $uninstall  = rex_get('uninstall', 'int', -1);
  $delete     = rex_get('delete', 'int', -1);
  $move       = rex_get('move', 'string', '');

  $redirect = false;

  // ----------------- ADDON INSTALL
  if ($install == 1)
  {
    if($pluginname != '')
    {
      if(($warning = $addonManager->install($pluginname)) === true)
      {
        $info = $REX['I18N']->msg("plugin_installed", $pluginname);
      }
    }
    else if (($warning = $addonManager->install($addonname)) === true)
    {
      $info = $REX['I18N']->msg("addon_installed", $addonname);
    }
  }
  // ----------------- ADDON ACTIVATE
  elseif ($activate == 1)
  {
    if($pluginname != '')
    {
      if(($warning = $addonManager->activate($pluginname)) === true)
      {
        $info = $REX['I18N']->msg("plugin_activated", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->activate($addonname)) === true)
    {
      $info = $REX['I18N']->msg("addon_activated", $addonname);
      $redirect = true;
    }
  }
  // ----------------- ADDON DEACTIVATE
  elseif ($activate == 0)
  {
    if($pluginname != '')
    {
      if (($warning = $addonManager->deactivate($pluginname)) === true)
      {
        $info = $REX['I18N']->msg("plugin_deactivated", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->deactivate($addonname)) === true)
    {
      $info = $REX['I18N']->msg("addon_deactivated", $addonname);
      $redirect = true;
    }
  }
  // ----------------- ADDON UNINSTALL
  elseif ($uninstall == 1)
  {
    if($pluginname != '')
    {
      if (($warning = $addonManager->uninstall($pluginname)) === true)
      {
        $info = $REX['I18N']->msg("plugin_uninstalled", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->uninstall($addonname)) === true)
    {
      $info = $REX['I18N']->msg("addon_uninstalled", $addonname);
      $redirect = true;
    }
  }
  // ----------------- ADDON DELETE
  elseif ($delete == 1)
  {
    if($pluginname != '')
    {
      if (($warning = $addonManager->delete($pluginname)) === true)
      {
        $info = $REX['I18N']->msg("plugin_deleted", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->delete($addonname)) === true)
    {
      $info = $REX['I18N']->msg("addon_deleted", $addonname);
      $redirect = true;
    }
  }

  if ($redirect)
  {
    header('Location: index.php?page=addon&info='. $info);
    exit;
  }
}

// ----------------- OUT
if ($subpage == '')
{
  if ($info != '')
    echo rex_info(htmlspecialchars($info));

  if ($warning != '' && $warning !== true)
    echo rex_warning($warning);

  if (!isset ($user_id))
  {
    $user_id = '';
  }
  echo '
      <table class="rex-table" summary="'.$REX['I18N']->msg("addon_summary").'">
      <caption>'.$REX['I18N']->msg("addon_caption").'</caption>
  	  <thead>
        <tr>
          <th class="rex-icon rex-col-a">&nbsp;</th>
          <th class="rex-col-b">'.$REX['I18N']->msg("addon_hname").'</th>
          <th class="rex-col-c">'.$REX['I18N']->msg("addon_hinstall").'</th>
          <th class="rex-col-d">'.$REX['I18N']->msg("addon_hactive").'</th>
          <th class="rex-col-e" colspan="2" rex-col-last>'.$REX['I18N']->msg("addon_hdelete").'</th>
        </tr>
  	  </thead>
  	  <tbody>';

  foreach (rex_ooAddon::getRegisteredAddons() as $addon)
  {
    // load package infos, especially for un-available addons
    rex_addonManager::loadPackage($addon);

    $addonVers = rex_ooAddon::getVersion($addon, '');
    $addonurl = 'index.php?page=addon&amp;addonname='.$addon.'&amp;';

  	if (rex_ooAddon::isSystemAddon($addon))
  	{
  		$delete = $REX['I18N']->msg("addon_systemaddon");
  	}
    else
  	{
  		$delete = '<a href="'. $addonurl .'delete=1" onclick="return confirm(\''.htmlspecialchars($REX['I18N']->msg('addon_delete_question', $addon)).'\');">'.$REX['I18N']->msg("addon_delete").'</a>';
  	}

    if (rex_ooAddon::isInstalled($addon))
    {
      $install = $REX['I18N']->msg("addon_yes").' - <a href="'. $addonurl .'install=1">'.$REX['I18N']->msg("addon_reinstall").'</a>';
      if(count(rex_ooPlugin::getInstalledPlugins($addon)) > 0)
      {
        $uninstall = $REX['I18N']->msg("plugin_plugins_installed");
        $delete = $REX['I18N']->msg("plugin_plugins_installed");
      }
      else
      {
        $uninstall = '<a href="'. $addonurl .'uninstall=1" onclick="return confirm(\''.htmlspecialchars($REX['I18N']->msg("addon_uninstall_question", $addon)).'\');">'.$REX['I18N']->msg("addon_uninstall").'</a>';
      }
    }
    else
    {
      $install = $REX['I18N']->msg("addon_no").' - <a href="'. $addonurl .'install=1">'.$REX['I18N']->msg("addon_install").'</a>';
      $uninstall = $REX['I18N']->msg("addon_notinstalled");
    }

    if (rex_ooAddon::isActivated($addon))
    {
      $status = $REX['I18N']->msg("addon_yes").' - <a href="'. $addonurl .'activate=0">'.$REX['I18N']->msg("addon_deactivate").'</a>';
    }
    elseif (rex_ooAddon::isInstalled($addon))
    {
      $status = $REX['I18N']->msg("addon_no").' - <a href="'. $addonurl .'activate=1">'.$REX['I18N']->msg("addon_activate").'</a>';
    }
    else
    {
      $status = $REX['I18N']->msg("addon_notinstalled");
    }

    echo '
        <tr class="rex-addon">
          <td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-addon"><span class="rex-i-element-in">'. htmlspecialchars($addon) .'</span></span></td>
          <td class="rex-col-b">'.htmlspecialchars($addon).' '. $addonVers .' [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'">?</a>]</td>
          <td class="rex-col-c">'.$install.'</td>
          <td class="rex-col-d">'.$status.'</td>
          <td class="rex-col-e">'.$uninstall.'</td>
          <td class="rex-col-f rex-col-last">'.$delete.'</td>
        </tr>'."\n   ";

    if(rex_ooAddon::isAvailable($addon))
    {
      foreach(rex_ooPlugin::getRegisteredPlugins($addon) as $plugin)
      {
        // load package infos, especially for un-available plugin
        rex_pluginManager::loadPackage($addon, $plugin);

        $pluginVers = rex_ooPlugin::getVersion($addon, $plugin, '');
        $pluginurl = 'index.php?page=addon&amp;addonname='.$addon.'&amp;pluginname='. $plugin .'&amp;';

        $delete = '<a href="'. $pluginurl .'delete=1" onclick="return confirm(\''.htmlspecialchars($REX['I18N']->msg('plugin_delete_question', $plugin)).'\');">'.$REX['I18N']->msg("addon_delete").'</a>';

        if (rex_ooPlugin::isInstalled($addon, $plugin))
        {
          $install = $REX['I18N']->msg("addon_yes").' - <a href="'. $pluginurl .'install=1">'.$REX['I18N']->msg("addon_reinstall").'</a>';
          $uninstall = '<a href="'. $pluginurl .'uninstall=1" onclick="return confirm(\''.htmlspecialchars($REX['I18N']->msg("plugin_uninstall_question", $plugin)).'\');">'.$REX['I18N']->msg("addon_uninstall").'</a>';
        }
        else
        {
          $install = $REX['I18N']->msg("addon_no").' - <a href="'. $pluginurl .'install=1">'.$REX['I18N']->msg("addon_install").'</a>';
          $uninstall = $REX['I18N']->msg("addon_notinstalled");
        }

        if (rex_ooPlugin::isActivated($addon, $plugin))
        {
          $status = $REX['I18N']->msg("addon_yes").' - <a href="'. $pluginurl .'activate=0">'.$REX['I18N']->msg("addon_deactivate").'</a>';
        }
        elseif (rex_ooPlugin::isInstalled($addon, $plugin))
        {
          $status = $REX['I18N']->msg("addon_no").' - <a href="'. $pluginurl .'activate=1">'.$REX['I18N']->msg("addon_activate").'</a>';
        }
        else
        {
          $status = $REX['I18N']->msg("addon_notinstalled");
        }

        echo '
            <tr class="rex-plugin">
              <td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-plugin"><span class="rex-i-element-in">'. htmlspecialchars($plugin) .'</span></span></td>
              <td class="rex-col-b">'.htmlspecialchars($plugin).' '. $pluginVers .' [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'&amp;pluginname='.$plugin.'">?</a>]</td>
              <td class="rex-col-c">'.$install.'</td>
              <td class="rex-col-d">'.$status.'</td>
              <td class="rex-col-e">'.$uninstall.'</td>
              <td class="rex-col-f rex-col-last">'.$delete.'</td>
            </tr>'."\n   ";
      }
    }
  }

  echo '</tbody>
  		</table>';
}
