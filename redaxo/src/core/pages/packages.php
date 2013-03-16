<?php
/**
 *
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('addons'), '');


$content = '';

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
    $credits .= '<dl class="rex-credits-info rex-formatted">';
    $credits .= '<dt>' . rex_i18n::msg('credits_name') . '</dt><dd>' . htmlspecialchars($name) . '</dd>';

    if ($version)     $credits .= '<dt>' . rex_i18n::msg('credits_version') . '</dt><dd>' . $version . '</dd>';
    if ($author)      $credits .= '<dt>' . rex_i18n::msg('credits_author') . '</dt><dd>' . htmlspecialchars($author) . '</dd>';
    if ($supportPage) $credits .= '<dt>' . rex_i18n::msg('credits_supportpage') . '</dt><dd><a href="http://' . $supportPage . '" onclick="window.open(this.href); return false;">' . $supportPage . '</a></dd>';

    $credits .= '</dl>';

    $content .= '<h2>' . rex_i18n::msg('package_help') . ' ' . $name . '</h2>';

    if (!is_file($package->getPath('help.php'))) {
        $content .= '<div class="rex-content-information">' . rex_i18n::msg('package_no_help_file') . '</div>';
    } else {
        ob_start();
        $package->includeFile('help.php');
        $content .= ob_get_clean();
    }

    echo rex_view::contentBlock($content, '', false);


    $content = '';
    $content .= '<h2>' . rex_i18n::msg('credits') . '</h2>';
    $content .= $credits;

    echo rex_view::contentBlock($content, '', false);

    echo '<a class="rex-back" href="javascript:history.back();"><span class="rex-icon rex-icon-back"></span>' . rex_i18n::msg('package_back') . '</a>';

}

// ----------------- OUT
if ($subpage == '') {
    rex_package_manager::synchronizeWithFileSystem();

    $content .= '
            <table class="rex-table rex-table-middle" id="rex-table-addons" summary="' . rex_i18n::msg('package_summary') . '">
            <caption>' . rex_i18n::msg('package_caption') . '</caption>
            <thead>
                <tr>
                    <th class="rex-slim">&nbsp;</th>
                    <th class="rex-name">' . rex_i18n::msg('package_hname') . '</th>
                    <th class="rex-install">' . rex_i18n::msg('package_hinstall') . '</th>
                    <th class="rex-active">' . rex_i18n::msg('package_hactive') . '</th>
                    <th class="rex-delete" colspan="2">' . rex_i18n::msg('package_hdelete') . '</th>
                </tr>
            </thead>
            <tbody>';

    $getLink = function (rex_package $package, $function, $confirm = false, $key = null) {
        $onclick = '';
        if ($confirm) {
            $onclick = ' data-confirm="' . rex_i18n::msg($package->getType() . '_' . $function . '_question', $package->getName()) . '"';
        }
        $text = rex_i18n::msg('package_' . ($key ?: $function));
        $url = rex_url::currentBackendPage([
            'package' => $package->getPackageId(),
            'rex-api-call' => 'package',
            'function' => $function
        ]);
        $class = ($key ?: $function);
        return '<a class="rex-' . $class . '" href="' . $url . '"' . $onclick . '>' . $text . '</a>';
    };

    $getTableRow = function (rex_package $package) use ($getLink) {
        $packageId = $package->getPackageId();
        $type = $package->getType();

        $delete = $package->isSystemPackage() ? '<span class="rex-muted rex-small">' . rex_i18n::msg($type . '_system' . $type) . '</span>' : $getLink($package, 'delete', true);

        $uninstall = '';
        if ($package->isInstalled()) {
            $install = '<span class="rex-icon rex-icon-active-true"></span> ' . $getLink($package, 'install', false, 'reinstall');
            $uninstall = '<span class="rex-icon rex-icon-uninstall"></span> ' . $getLink($package, 'uninstall', true);
        } else {
            $install = '<span class="rex-icon rex-icon-install"></span> ' . $getLink($package, 'install');
            //$uninstall = rex_i18n::msg('package_notinstalled');
        }

        $status = '';
        if ($package->isActivated()) {
            $status = '<span class="rex-icon rex-icon-active-true"></span> ' . $getLink($package, 'deactivate');
        } elseif ($package->isInstalled()) {
            $status = '<span class="rex-icon rex-icon-active-false"></span> ' . $getLink($package, 'activate');
        } else {
            //$status = rex_i18n::msg('package_notinstalled');
        }
        $name = '<span class="rex-' . $type . '-name">' . htmlspecialchars($package->getName()) . '</span>';
        $class = $package->isSystemPackage() ? ' rex-system-' . $type : '';

        // --------------------------------------------- API MESSAGES
        $message = '';
        if ($package->getPackageId() == rex_get('package', 'string') && rex_api_function::hasMessage()) {
            $message = '
                    <tr class="rex-' . $type . $class . ' rex-message">
                        <td></td>
                        <td colspan="5">
                             ' . rex_api_function::getMessage() . '
                        </td>
                    </tr>';
        }

        if (trim($package->getVersion()) != '') {
            $name .= ' <span class="rex-' . $type . '-version">' . trim($package->getVersion()) . '</span>';
        }

        return $message . '
                    <tr class="rex-' . $type . $class . '">
                        <td class="rex-slim"><span class="rex-icon rex-icon-' . $type . '"></span></td>
                        <td class="rex-package-name"><a href="' . rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '">' . $name . ' <span class="rex-icon rex-icon-help"></span></a></td>
                        <td class="rex-install">' . $install . '</td>
                        <td class="rex-active" data-pjax-container="#rex-page">' . $status . '</td>
                        <td class="rex-uninstall" data-pjax-container="#rex-page">' . $uninstall . '</td>
                        <td class="rex-delete" data-pjax-container="#rex-page">' . $delete . '</td>
                    </tr>' . "\n   ";
    };

    foreach (rex_addon::getRegisteredAddons() as $addonName => $addon) {
        $content .= $getTableRow($addon);

        if ($addon->isActivated()) {
            foreach ($addon->getRegisteredPlugins() as $pluginName => $plugin) {
                $content .= $getTableRow($plugin);
            }
        }
    }

    $content .= '</tbody>
            </table>';


    echo rex_view::contentBlock($content);
}
