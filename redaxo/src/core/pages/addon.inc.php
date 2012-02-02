<?php
/**
 *
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('addon'), '');

// -------------- RequestVars
$subpage = rex_request('subpage', 'string');

// ----------------- HELPPAGE
if ($subpage == 'help')
{
  $package     = rex_package::get(rex_request('package', 'string'));
  $name        = $package->getPackageId();
  $version     = $package->getVersion();
  $author      = $package->getAuthor();
  $supportPage = $package->getSupportPage();

  $credits = '';
  $credits .= rex_i18n::msg("credits_name") .': <span>'. htmlspecialchars($name) .'</span><br />';
  if($version) $credits .= rex_i18n::msg("credits_version") .': <span>'. $version .'</span><br />';
  if($author) $credits .= rex_i18n::msg("credits_author") .': <span>'. htmlspecialchars($author) .'</span><br />';
  if($supportPage) $credits .= rex_i18n::msg("credits_supportpage") .': <span><a href="http://'.$supportPage.'" onclick="window.open(this.href); return false;">'. $supportPage .'</a></span><br />';

  echo '<div class="rex-area">
  			<h3 class="rex-hl2">'.rex_i18n::msg("addon_help").' '.$name.'</h3>
	  		<div class="rex-area-content">';
  if (!is_file($package->getBasePath('help.inc.php')))
  {
    echo '<p>'. rex_i18n::msg("addon_no_help_file") .'</p>';
  }
  else
  {
    rex_package_manager::includeFile($package, 'help.inc.php');
  }
  echo '<br />
        <p id="rex-addon-credits">'. $credits .'</p>
        </div>
  			<div class="rex-area-footer">
  				<p><a href="javascript:history.back();">'.rex_i18n::msg("addon_back").'</a></p>
  			</div>
  		</div>';
}

// ----------------- OUT
if ($subpage == '')
{
  rex_package_manager::synchronizeWithFileSystem();

  echo '
      <table class="rex-table" id="rex-table-addon" summary="'.rex_i18n::msg("addon_summary").'">
      <caption>'.rex_i18n::msg("addon_caption").'</caption>
  	  <thead>
        <tr>
          <th class="rex-icon">&nbsp;</th>
          <th class="rex-name">'.rex_i18n::msg("addon_hname").'</th>
          <th class="rex-install">'.rex_i18n::msg("addon_hinstall").'</th>
          <th class="rex-active">'.rex_i18n::msg("addon_hactive").'</th>
          <th class="rex-function" colspan="2">'.rex_i18n::msg("addon_hdelete").'</th>
        </tr>
  	  </thead>
  	  <tbody>';

  foreach (rex_addon::getRegisteredAddons() as $addonName => $addon)
  {
    echo rex_api_package::getTableRow($addon);

    $hide = !$addon->isActivated();
    foreach($addon->getRegisteredPlugins() as $pluginName => $plugin)
    {
      echo rex_api_package::getTableRow($plugin, $hide);
    }
  }

  echo '</tbody>
  		</table>';
}