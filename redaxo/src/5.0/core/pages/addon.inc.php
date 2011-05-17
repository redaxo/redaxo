<?php
/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

rex_title(rex_i18n::msg('addon'), '');

// -------------- RequestVars
$addonname  = rex_request('addonname', 'string');
$pluginname = rex_request('pluginname', 'string');
$subpage    = rex_request('subpage', 'string');
$info       = rex_request('info', 'string');
$warning = '';

$redirect = false;

// -------------- CHECK IF CONFIG FILES ARE UP2DATE
if ($subpage == '')
{
  rex_package_manager::synchronizeWithFileSystem();
}

// -------------- Sanity checks
$addonname  = rex_addon::exists($addonname) ? $addonname : '';
if($addonname != '')
  $pluginname = rex_plugin::exists($addonname, $pluginname) ? $pluginname : '';
else
  $pluginname = '';

if($pluginname != '')
{
  $package = rex_plugin::get($addonname, $pluginname);
  $addonManager = rex_plugin_manager::factory($package);
}
elseif($addonname != '')
{
  $package = rex_addon::get($addonname);
  $addonManager = rex_addon_manager::factory($package);
}

// ----------------- HELPPAGE
if ($subpage == 'help' && $addonname != '')
{
  $helpfile    = $package->getBasePath('help.inc.php');
  $version     = $package->getVersion($addonname);
  $author      = $package->getAuthor($addonname);
  $supportPage = $package->getSupportPage($addonname);
  if($pluginname != '')
  {
    $addonname   = $addonname .' / '. $pluginname;
  }

  $credits = '';
  $credits .= rex_i18n::msg("credits_name") .': <span>'. htmlspecialchars($addonname) .'</span><br />';
  if($version) $credits .= rex_i18n::msg("credits_version") .': <span>'. $version .'</span><br />';
  if($author) $credits .= rex_i18n::msg("credits_author") .': <span>'. htmlspecialchars($author) .'</span><br />';
  if($supportPage) $credits .= rex_i18n::msg("credits_supportpage") .': <span><a href="http://'.$supportPage.'" onclick="window.open(this.href); return false;">'. $supportPage .'</a></span><br />';

  echo '<div class="rex-area">
  			<h3 class="rex-hl2">'.rex_i18n::msg("addon_help").' '.$addonname.'</h3>
	  		<div class="rex-area-content">';
  if (!is_file($helpfile))
  {
    echo '<p>'. rex_i18n::msg("addon_no_help_file") .'</p>';
  }
  else
  {
    include $helpfile;
  }
  echo '<br />
        <p id="rex-addon-credits">'. $credits .'</p>
        </div>
  			<div class="rex-area-footer">
  				<p><a href="javascript:history.back();">'.rex_i18n::msg("addon_back").'</a></p>
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

  // ----------------- ADDON INSTALL
  if ($install == 1)
  {
    if($pluginname != '')
    {
      if(($warning = $addonManager->install()) === true)
      {
        $info = rex_i18n::msg("plugin_installed", $pluginname);
      }
    }
    else if (($warning = $addonManager->install()) === true)
    {
      $info = rex_i18n::msg("addon_installed", $addonname);
    }
  }
  // ----------------- ADDON ACTIVATE
  elseif ($activate == 1)
  {
    if($pluginname != '')
    {
      if(($warning = $addonManager->activate()) === true)
      {
        $info = rex_i18n::msg("plugin_activated", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->activate()) === true)
    {
      $info = rex_i18n::msg("addon_activated", $addonname);
      $redirect = true;
    }
  }
  // ----------------- ADDON DEACTIVATE
  elseif ($activate == 0)
  {
    if($pluginname != '')
    {
      if (($warning = $addonManager->deactivate()) === true)
      {
        $info = rex_i18n::msg("plugin_deactivated", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->deactivate()) === true)
    {
      $info = rex_i18n::msg("addon_deactivated", $addonname);
      $redirect = true;
    }
  }
  // ----------------- ADDON UNINSTALL
  elseif ($uninstall == 1)
  {
    if($pluginname != '')
    {
      if (($warning = $addonManager->uninstall()) === true)
      {
        $info = rex_i18n::msg("plugin_uninstalled", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->uninstall()) === true)
    {
      $info = rex_i18n::msg("addon_uninstalled", $addonname);
      $redirect = true;
    }
  }
  // ----------------- ADDON DELETE
  elseif ($delete == 1)
  {
    if($pluginname != '')
    {
      if (($warning = $addonManager->delete()) === true)
      {
        $info = rex_i18n::msg("plugin_deleted", $pluginname);
        $redirect = true;
      }
    }
    else if (($warning = $addonManager->delete()) === true)
    {
      $info = rex_i18n::msg("addon_deleted", $addonname);
      $redirect = true;
    }
  }

}

if ($redirect)
{
  header('Location: index.php?page=addon&info='. $info);
  exit;
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
      <table class="rex-table" summary="'.rex_i18n::msg("addon_summary").'">
      <caption>'.rex_i18n::msg("addon_caption").'</caption>
  	  <thead>
        <tr>
          <th class="rex-icon rex-col-a">&nbsp;</th>
          <th class="rex-col-b">'.rex_i18n::msg("addon_hname").'</th>
          <th class="rex-col-c">'.rex_i18n::msg("addon_hinstall").'</th>
          <th class="rex-col-d">'.rex_i18n::msg("addon_hactive").'</th>
          <th class="rex-col-e" colspan="2" rex-col-last>'.rex_i18n::msg("addon_hdelete").'</th>
        </tr>
  	  </thead>
  	  <tbody>';

  foreach (rex_addon::getRegisteredAddons() as $addonName => $addon)
  {
    // load package infos, especially for un-available addons
    rex_addon_manager::loadPackageInfos($addon);

    $addonVers = $addon->getVersion('');
    $addonurl = 'index.php?page=addon&amp;addonname='.$addonName.'&amp;';

  	if ($addon->isSystemPackage())
  	{
  		$delete = rex_i18n::msg("addon_systemaddon");
  	}
    else
  	{
  		$delete = '<a href="'. $addonurl .'delete=1" onclick="return confirm(\''.htmlspecialchars(rex_i18n::msg('addon_delete_question', $addonName)).'\');">'.rex_i18n::msg("addon_delete").'</a>';
  	}

    if ($addon->isInstalled())
    {
      $install = rex_i18n::msg("addon_yes").' - <a href="'. $addonurl .'install=1">'.rex_i18n::msg("addon_reinstall").'</a>';
      if(count($addon->getInstalledPlugins()) > 0)
      {
        $uninstall = rex_i18n::msg("plugin_plugins_installed");
        $delete = rex_i18n::msg("plugin_plugins_installed");
      }
      else
      {
        $uninstall = '<a href="'. $addonurl .'uninstall=1" onclick="return confirm(\''.htmlspecialchars(rex_i18n::msg("addon_uninstall_question", $addonName)).'\');">'.rex_i18n::msg("addon_uninstall").'</a>';
      }
    }
    else
    {
      $install = rex_i18n::msg("addon_no").' - <a href="'. $addonurl .'install=1">'.rex_i18n::msg("addon_install").'</a>';
      $uninstall = rex_i18n::msg("addon_notinstalled");
    }

    if ($addon->isActivated())
    {
      $status = rex_i18n::msg("addon_yes").' - <a href="'. $addonurl .'activate=0">'.rex_i18n::msg("addon_deactivate").'</a>';
    }
    elseif ($addon->isInstalled())
    {
      $status = rex_i18n::msg("addon_no").' - <a href="'. $addonurl .'activate=1">'.rex_i18n::msg("addon_activate").'</a>';
    }
    else
    {
      $status = rex_i18n::msg("addon_notinstalled");
    }

    echo '
        <tr class="rex-addon">
          <td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-addon"><span class="rex-i-element-in">'. htmlspecialchars($addonName) .'</span></span></td>
          <td class="rex-col-b">'.htmlspecialchars($addonName).' '. $addonVers .' [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addonName.'">?</a>]</td>
          <td class="rex-col-c">'.$install.'</td>
          <td class="rex-col-d">'.$status.'</td>
          <td class="rex-col-e">'.$uninstall.'</td>
          <td class="rex-col-f rex-col-last">'.$delete.'</td>
        </tr>'."\n   ";

    if($addon->isAvailable())
    {
      foreach($addon->getRegisteredPlugins() as $pluginName => $plugin)
      {
        // load package infos, especially for un-available plugin
        rex_plugin_manager::loadPackageInfos($plugin);

        $pluginVers = $plugin->getVersion();
        $pluginurl = 'index.php?page=addon&amp;addonname='.$addonName.'&amp;pluginname='. $pluginName .'&amp;';

        if ($plugin->isSystemPackage())
      	{
      		$delete = rex_i18n::msg("plugin_systemplugin");
      	}
      	else
      	{
      	  $delete = '<a href="'. $pluginurl .'delete=1" onclick="return confirm(\''.htmlspecialchars(rex_i18n::msg('plugin_delete_question', $pluginName)).'\');">'.rex_i18n::msg("addon_delete").'</a>';
      	}

        if ($plugin->isInstalled())
        {
          $install = rex_i18n::msg("addon_yes").' - <a href="'. $pluginurl .'install=1">'.rex_i18n::msg("addon_reinstall").'</a>';
          $uninstall = '<a href="'. $pluginurl .'uninstall=1" onclick="return confirm(\''.htmlspecialchars(rex_i18n::msg("plugin_uninstall_question", $pluginName)).'\');">'.rex_i18n::msg("addon_uninstall").'</a>';
        }
        else
        {
          $install = rex_i18n::msg("addon_no").' - <a href="'. $pluginurl .'install=1">'.rex_i18n::msg("addon_install").'</a>';
          $uninstall = rex_i18n::msg("addon_notinstalled");
        }

        if ($plugin->isActivated())
        {
          $status = rex_i18n::msg("addon_yes").' - <a href="'. $pluginurl .'activate=0">'.rex_i18n::msg("addon_deactivate").'</a>';
        }
        elseif ($plugin->isInstalled())
        {
          $status = rex_i18n::msg("addon_no").' - <a href="'. $pluginurl .'activate=1">'.rex_i18n::msg("addon_activate").'</a>';
        }
        else
        {
          $status = rex_i18n::msg("addon_notinstalled");
        }

        echo '
            <tr class="rex-plugin">
              <td class="rex-icon rex-col-a"><span class="rex-i-element rex-i-plugin"><span class="rex-i-element-in">'. htmlspecialchars($pluginName) .'</span></span></td>
              <td class="rex-col-b">'.htmlspecialchars($pluginName).' '. $pluginVers .' [<a href="index.php?page=addon&amp;subpage=help&amp;addonname='.$addonName.'&amp;pluginname='.$pluginName.'">?</a>]</td>
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
