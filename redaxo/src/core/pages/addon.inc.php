<?php
/**
 *
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('addons'), '');

// -------------- RequestVars
$subpage = rex_request('subpage', 'string');

// ----------------- HELPPAGE
if ($subpage == 'help') {
  $package     = rex_package::get(rex_request('package', 'string'));
  $name        = $package->getPackageId();
  $version     = $package->getVersion();
  $author      = $package->getAuthor();
  $supportPage = $package->getSupportPage();

  $credits = '';
  $credits .= rex_i18n::msg('credits_name') . ': <span>' . htmlspecialchars($name) . '</span><br />';
  if ($version) $credits .= rex_i18n::msg('credits_version') . ': <span>' . $version . '</span><br />';
  if ($author) $credits .= rex_i18n::msg('credits_author') . ': <span>' . htmlspecialchars($author) . '</span><br />';
  if ($supportPage) $credits .= rex_i18n::msg('credits_supportpage') . ': <span><a href="http://' . $supportPage . '" onclick="window.open(this.href); return false;">' . $supportPage . '</a></span><br />';

  echo '<div class="rex-area">
        <h3 class="rex-hl2">' . rex_i18n::msg('addon_help') . ' ' . $name . '</h3>
        <div class="rex-area-content">';
  if (!is_file($package->getBasePath('help.inc.php'))) {
    echo '<p>' . rex_i18n::msg('addon_no_help_file') . '</p>';
  } else {
    rex_package_manager::includeFile($package, 'help.inc.php');
  }
  echo '<br />
        <p id="rex-addon-credits">' . $credits . '</p>
        </div>
        <div class="rex-area-footer">
          <p><a href="javascript:history.back();">' . rex_i18n::msg('addon_back') . '</a></p>
        </div>
      </div>';
}

// ----------------- OUT
if ($subpage == '') {
  rex_package_manager::synchronizeWithFileSystem();

  echo '
      <table class="rex-table" id="rex-table-addons" summary="' . rex_i18n::msg('addon_summary') . '">
      <caption>' . rex_i18n::msg('addon_caption') . '</caption>
      <thead>
        <tr>
          <th class="rex-icon">&nbsp;</th>
          <th class="rex-name">' . rex_i18n::msg('addon_hname') . '</th>
          <th class="rex-install">' . rex_i18n::msg('addon_hinstall') . '</th>
          <th class="rex-active">' . rex_i18n::msg('addon_hactive') . '</th>
          <th class="rex-function" colspan="2">' . rex_i18n::msg('addon_hdelete') . '</th>
        </tr>
      </thead>
      <tbody>';

  $getLink = function (rex_package $package, $function, $confirm = false, $key = null) {
    $onclick = '';
    if ($confirm) {
      $onclick = ' data-confirm="' . rex_i18n::msg($package->getType() . '_' . $function . '_question', $package->getName()) . '"';
    }
    $text = rex_i18n::msg('addon_' . ($key ?: $function));
    return '<a href="index.php?page=addon&amp;package=' . $package->getPackageId() . '&amp;rex-api-call=package&amp;function=' . $function . '"' . $onclick . '>' . $text . '</a>';
  };

  $getTableRow = function (rex_package $package) use ($getLink) {
    $packageId = $package->getPackageId();
    $type = $package->getType();

    $delete = $package->isSystemPackage() ? rex_i18n::msg($type . '_system' . $type) : $getLink($package, 'delete', true);

    if ($package->isInstalled()) {
      $install = rex_i18n::msg('addon_yes') . ' - ' . $getLink($package, 'install', false, 'reinstall');
      if ($type == 'addon' && count($package->getInstalledPlugins()) > 0) {
        $uninstall = rex_i18n::msg('plugin_plugins_installed');
        $delete = rex_i18n::msg('plugin_plugins_installed');
      } else {
        $uninstall = $getLink($package, 'uninstall', true);
      }
    } else {
      $install = rex_i18n::msg('addon_no') . ' - ' . $getLink($package, 'install');
      $uninstall = rex_i18n::msg('addon_notinstalled');
    }

    if ($package->isActivated()) {
      $status = rex_i18n::msg('addon_yes') . ' - ' . $getLink($package, 'deactivate');
    } elseif ($package->isInstalled()) {
      $status = rex_i18n::msg('addon_no') . ' - ' . $getLink($package, 'activate');
    } else {
      $status = rex_i18n::msg('addon_notinstalled');
    }
    $name = htmlspecialchars($package->getName());
    $class = str_replace(array('.', '/'), '_', $packageId);

    // --------------------------------------------- API MESSAGES
    $message = '';
    if ($package->getPackageId() == rex_get('package', 'string') && rex_api_function::hasMessage()) {
      $message = '
          <tr class="rex-package-message rex-warning">
            <td class="rex-warning"></td>
            <td colspan="5">
               ' . rex_api_function::getMessage(false) . '
            </td>
          </tr>';
    }

    return $message . '
          <tr class="rex-' . $type . ' rex-' . $type . '-' . $class . '">
            <td class="rex-icon"><span class="rex-ic-' . $type . '">' . $name . '</span></td>
            <td class="rex-name">' . $name . ' ' . $package->getVersion() . ' <a href="index.php?page=addon&amp;subpage=help&amp;package=' . $packageId . '">?</a></td>
            <td class="rex-install">' . $install . '</td>
            <td class="rex-active" data-pjax-container="#rex-page">' . $status . '</td>
            <td class="rex-uninstall" data-pjax-container="#rex-page">' . $uninstall . '</td>
            <td class="rex-delete" data-pjax-container="#rex-page">' . $delete . '</td>
          </tr>' . "\n   ";
  };

  foreach (rex_addon::getRegisteredAddons() as $addonName => $addon) {
    echo $getTableRow($addon);

    if ($addon->isActivated()) {
      foreach ($addon->getRegisteredPlugins() as $pluginName => $plugin) {
        echo $getTableRow($plugin);
      }
    }
  }

  echo '</tbody>
      </table>';
}
