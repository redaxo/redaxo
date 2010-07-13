<?php
/**
 *
 * @package redaxo4
 * @version svn:$Id$
 */

rex_title($I18N->msg('addon'), '');

// -------------- RequestVars
$addonname  = rex_request('addonname', 'string');
$pluginname = rex_request('pluginname', 'string');
$subpage    = rex_request('subpage', 'string');
$info       = stripslashes(rex_request('info', 'string'));

// -------------- READ CONFIG
$ADDONS    = rex_read_addons_folder();
$PLUGINS   = array();
foreach($ADDONS as $_addon)
  $PLUGINS[$_addon] = rex_read_plugins_folder($_addon);
  
$addonname  = array_search($addonname, $ADDONS) !== false ? $addonname : '';
if($addonname != '')
  $pluginname = array_search($pluginname, $PLUGINS[$addonname]) !== false ? $pluginname : '';
else
  $pluginname = '';
  
$warning = '';

if($pluginname != '')
{
  $addonManager = new rex_pluginManager($PLUGINS, $addonname);
}
else
{
  $addonManager = new rex_addonManager($ADDONS);
}

// ----------------- HELPPAGE
if ($subpage == 'help' && $addonname != '')
{
  if($pluginname != '')
  {
    $helpfile    = rex_plugins_folder($addonname, $pluginname);
    $version     = OOPlugin::getVersion($addonname, $pluginname);
    $author      = OOPlugin::getAuthor($addonname, $pluginname);
    $supportPage = OOPlugin::getSupportPage($addonname, $pluginname);
    $addonname   = $addonname .' / '. $pluginname;
  }
  else
  {
    $helpfile    = rex_addons_folder($addonname);
    $version     = OOAddon::getVersion($addonname);
    $author      = OOAddon::getAuthor($addonname);
    $supportPage = OOAddon::getSupportPage($addonname);
  }
  $helpfile .= DIRECTORY_SEPARATOR.'help.inc.php';
  
  $credits = '';
  $credits .= $I18N->msg("credits_name") .': <span>'. htmlspecialchars($addonname) .'</span><br />';
  if($version) $credits .= $I18N->msg("credits_version") .': <span>'. $version .'</span><br />';
  if($author) $credits .= $I18N->msg("credits_author") .': <span>'. htmlspecialchars($author) .'</span><br />';
  if($supportPage) $credits .= $I18N->msg("credits_supportpage") .': <span><a href="http://'.$supportPage.'" onclick="window.open(this.href); return false;">'. $supportPage .'</a></span><br />';
  
  echo '<div class="rex-area">
  			<h3 class="rex-hl2">'.$I18N->msg("addon_help").' '.$addonname.'</h3>
	  		<div class="rex-area-content">';
  if (!is_file($helpfile))
  {
    echo '<p>'. $I18N->msg("addon_no_help_file") .'</p>';
  }
  else
  {
    include $helpfile;
  }
  echo '<br />
        <p id="rex-addon-credits">'. $credits .'</p>
        </div>
  			<div class="rex-area-footer">
  				<p><a href="JavaScript:history.back();">'.$I18N->msg("addon_back").'</a></p>
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
  
  $redirect = false;
  
  // ----------------- ADDON INSTALL
  if ($install == 1)
  {
    if($pluginname != '')
    {
      if(($warning = $addonManager->install($pluginname)) === true)
      {
        $info = $I18N->msg("plugin_installed", $pluginname);
      }
    }
    else if (($warning = $addonManager->install($addonname)) === true)
    {
      $info = $I18N->msg("addon_installed", $addonname);
    }
  }
  // ----------------- ADDON ACTIVATE
  elseif ($activate == 1)
  {
    if($pluginname != '')
    {
      if(($warning = $addonManager->activate($pluginname)) === true)
      {
        $info = $I18N->msg("plugin_activated", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->activate($addonname)) === true)
    {
      $info = $I18N->msg("addon_activated", $addonname);
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
        $info = $I18N->msg("plugin_deactivated", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->deactivate($addonname)) === true)
    {
      $info = $I18N->msg("addon_deactivated", $addonname);
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
        $info = $I18N->msg("plugin_uninstalled", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->uninstall($addonname)) === true)
    {
      $info = $I18N->msg("addon_uninstalled", $addonname);
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
        $info = $I18N->msg("plugin_deleted", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->delete($addonname)) === true)
    {
      $info = $I18N->msg("addon_deleted", $addonname);
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
  // Vergleiche Addons aus dem Verzeichnis addons/ mit den Eintraegen in include/addons.inc.php
  // Wenn ein Addon in der Datei fehlt oder nicht mehr vorhanden ist, aendere den Dateiinhalt.
  if (count(array_diff($ADDONS, OOAddon::getRegisteredAddons())) > 0 ||
      count(array_diff(OOAddon::getRegisteredAddons(), $ADDONS)) > 0)
  {
    if (($state = rex_generateAddons($ADDONS)) !== true)
    {
      $warning .= $state;
    }
  }

  // Vergleiche plugins aus dem Verzeichnis plugins/ mit den Eintraegen in include/plugins.inc.php
  // Wenn ein plugin in der Datei fehlt oder nicht mehr vorhanden ist, aendere den Dateiinhalt.
  foreach($ADDONS as $addon)
  {
    if (count(array_diff($PLUGINS[$addon], OOPlugin::getRegisteredPlugins($addon))) > 0 ||
        count(array_diff(OOPlugin::getRegisteredPlugins($addon), $PLUGINS[$addon])) > 0)
    {
      if (($state = rex_generateplugins($PLUGINS)) !== true)
      {
        $warning .= $state;
        break;
      }
    }
  }
  
  if ($info != '')
    echo rex_info($info);

  if ($warning != '' && $warning !== true)
    echo rex_warning($warning);

  if (!isset ($user_id))
  {
    $user_id = '';
  }
  echo '
      <table class="rex-table" summary="'.$I18N->msg("addon_summary").'">
      <caption>'.$I18N->msg("addon_caption").'</caption>
      <colgroup>
      	<col width="40" />
        <col width="*"/>
        <col width="130" />
        <col width="130" />
        <col width="130" />
        <col width="153" />
      </colgroup>
  	  <thead>
        <tr>
          <th class="rex-icon rex-col-a">&nbsp;</th>
          <th class="rex-col-b">'.$I18N->msg("addon_hname").'</th>
          <th class="rex-col-c">'.$I18N->msg("addon_hinstall").'</th>
          <th class="rex-col-d">'.$I18N->msg("addon_hactive").'</th>
          <th class="rex-col-e" colspan="2">'.$I18N->msg("addon_hdelete").'</th>
        </tr>
  	  </thead>
  	  <tbody>';

  foreach ($ADDONS as $addon)
  {
    $addonurl = 'index.php?page=addon&amp;addonname='.$addon.'&amp;';
    
  	if (OOAddon::isSystemAddon($addon))
  	{
  		$delete = $I18N->msg("addon_systemaddon");
  	}
    else
  	{
  		$delete = '<a href="'. $addonurl .'delete=1" onclick="return confirm(\''.htmlspecialchars($I18N->msg('addon_delete_question', $addon)).'\');">'.$I18N->msg("addon_delete").'</a>';
  	}

    if (OOAddon::isInstalled($addon))
    {
      $install = $I18N->msg("addon_yes").' - <a href="'. $addonurl .'install=1">'.$I18N->msg("addon_reinstall").'</a>';
      if(count(OOPlugin::getInstalledPlugins($addon)) > 0)
      {
        $uninstall = $I18N->msg("plugin_plugins_installed");
        $delete = $I18N->msg("plugin_plugins_installed");
      }
      else
      {
        $uninstall = '<a href="'. $addonurl .'uninstall=1" onclick="return confirm(\''.htmlspecialchars($I18N->msg("addon_uninstall_question", $addon)).'\');">'.$I18N->msg("addon_uninstall").'</a>';
      }
    }
    else
    {
      $install = $I18N->msg("addon_no").' - <a href="'. $addonurl .'install=1">'.$I18N->msg("addon_install").'</a>';
      $uninstall = $I18N->msg("addon_notinstalled");
    }

    if (OOAddon::isActivated($addon))
    {
      $status = $I18N->msg("addon_yes").' - <a href="'. $addonurl .'activate=0">'.$I18N->msg("addon_deactivate").'</a>';
    }
    elseif (OOAddon::isInstalled($addon))
    {
      $status = $I18N->msg("addon_no").' - <a href="'. $addonurl .'activate=1">'.$I18N->msg("addon_activate").'</a>';
    }
    else
    {
      $status = $I18N->msg("addon_notinstalled");
    }

    echo '
        <tr class="rex-addon">
          <td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-addon"><span class="rex-i-element-text">'. htmlspecialchars($addon) .'</span></span></td>
          <td class="rex-col-b">'.htmlspecialchars($addon).' [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'">?</a>]</td>
          <td class="rex-col-c">'.$install.'</td>
          <td class="rex-col-d">'.$status.'</td>
          <td class="rex-col-e">'.$uninstall.'</td>
          <td class="rex-col-f">'.$delete.'</td>
        </tr>'."\n   ";

    if(OOAddon::isAvailable($addon))
    {
      foreach($PLUGINS[$addon] as $plugin)
      {
        $pluginurl = 'index.php?page=addon&amp;addonname='.$addon.'&amp;pluginname='. $plugin .'&amp;';
        
        $delete = '<a href="'. $pluginurl .'delete=1" onclick="return confirm(\''.htmlspecialchars($I18N->msg('plugin_delete_question', $plugin)).'\');">'.$I18N->msg("addon_delete").'</a>';
        
        if (OOPlugin::isInstalled($addon, $plugin))
        {
          $install = $I18N->msg("addon_yes").' - <a href="'. $pluginurl .'install=1">'.$I18N->msg("addon_reinstall").'</a>';
          $uninstall = '<a href="'. $pluginurl .'uninstall=1" onclick="return confirm(\''.htmlspecialchars($I18N->msg("plugin_uninstall_question", $plugin)).'\');">'.$I18N->msg("addon_uninstall").'</a>';
        }
        else
        {
          $install = $I18N->msg("addon_no").' - <a href="'. $pluginurl .'install=1">'.$I18N->msg("addon_install").'</a>';
          $uninstall = $I18N->msg("addon_notinstalled");
        }
    
        if (OOPlugin::isActivated($addon, $plugin))
        {
          $status = $I18N->msg("addon_yes").' - <a href="'. $pluginurl .'activate=0">'.$I18N->msg("addon_deactivate").'</a>';
        }
        elseif (OOPlugin::isInstalled($addon, $plugin))
        {
          $status = $I18N->msg("addon_no").' - <a href="'. $pluginurl .'activate=1">'.$I18N->msg("addon_activate").'</a>';
        }
        else
        {
          $status = $I18N->msg("addon_notinstalled");
        }
        
        echo '
            <tr class="rex-plugin">
              <td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-plugin"><span class="rex-i-element-text">'. htmlspecialchars($plugin) .'</span></span></td>
              <td class="rex-col-b">'.htmlspecialchars($plugin).' [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addon.'&amp;pluginname='.$plugin.'">?</a>]</td>
              <td class="rex-col-c">'.$install.'</td>
              <td class="rex-col-d">'.$status.'</td>
              <td class="rex-col-e">'.$uninstall.'</td>
              <td class="rex-col-f">'.$delete.'</td>
            </tr>'."\n   ";
      }
    }
  }

  echo '</tbody>
  		</table>';
}