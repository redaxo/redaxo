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


    if (!is_file($package->getPath('help.php'))) {
        $content .= rex_view::info(rex_i18n::msg('package_no_help_file'));
    } else {
        ob_start();
        $package->includeFile('help.php');
        $content .= ob_get_clean();
    }

    $fragment = new rex_fragment();
    $fragment->setVar('heading', rex_i18n::msg('package_help') . ' ' . $name, false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');

    




    $credits = '';
    $credits .= '<dl class="rex-credits-info rex-dl-horizontal">';
    $credits .= '<dt>' . rex_i18n::msg('credits_name') . '</dt><dd>' . htmlspecialchars($name) . '</dd>';

    if ($version) {
        $credits .= '<dt>' . rex_i18n::msg('credits_version') . '</dt><dd>' . $version . '</dd>';
    }
    if ($author) {
        $credits .= '<dt>' . rex_i18n::msg('credits_author') . '</dt><dd>' . htmlspecialchars($author) . '</dd>';
    }
    if ($supportPage) {
        $credits .= '<dt>' . rex_i18n::msg('credits_supportpage') . '</dt><dd><a href="http://' . $supportPage . '" onclick="window.open(this.href); return false;">' . $supportPage . '</a></dd>';
    }

    $credits .= '</dl>';


    $fragment = new rex_fragment();
    $fragment->setVar('heading', rex_i18n::msg('credits'), false);
    $fragment->setVar('content', $credits, false);
    echo $fragment->parse('core/page/section.php');


    echo '<a class="rex-button rex-button-back" href="javascript:history.back();"><i class="rex-icon rex-icon-back"></i> ' . rex_i18n::msg('package_back') . '</a>';

}

// ----------------- OUT
if ($subpage == '') {
    rex_package_manager::synchronizeWithFileSystem();

    $content .= '
            <table class="rex-table rex-table-responsive" id="rex-table-packages">
            <caption>' . rex_i18n::msg('package_caption') . '</caption>
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>' . rex_i18n::msg('package_hname') . '</th>
                    <th>' . rex_i18n::msg('package_hinstall') . '</th>
                    <th>' . rex_i18n::msg('package_hactive') . '</th>
                    <th colspan="2">' . rex_i18n::msg('package_hdelete') . '</th>
                </tr>
            </thead>
            <tbody>';

    $getLink = function (rex_package $package, $function, $icon = '', $confirm = false, $key = null) {
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

        $icon = ($icon != '') ? '<i class="rex-icon ' . $icon . '"></i>' : '';
        $class = ($key ?: $function);
        return '<a class="rex-' . $class . '" href="' . $url . '"' . $onclick . '>' . $icon . ' ' . $text . '</a>';
    };

    $getTableRow = function (rex_package $package) use ($getLink) {
        $packageId = $package->getPackageId();
        $type = $package->getType();

        $delete = $package->isSystemPackage() ? '<small class="rex-text-muted">' . rex_i18n::msg($type . '_system' . $type) . '</small>' : $getLink($package, 'delete', 'rex-icon-package-delete', true);

        $uninstall = '&nbsp;';
        if ($package->isInstalled()) {
            $install = $getLink($package, 'install', 'rex-icon-package-is-installed', false, 'reinstall');
            $uninstall = $getLink($package, 'uninstall', 'rex-icon-package-uninstall', true);
        } else {
            $install = $getLink($package, 'install', 'rex-icon-package-not-installed');
            //$uninstall = rex_i18n::msg('package_notinstalled');
        }

        $status = '&nbsp;';
        if ($package->isActivated()) {
            $status = $getLink($package, 'deactivate', 'rex-icon-package-is-activated');
        } elseif ($package->isInstalled()) {
            $status = $getLink($package, 'activate', 'rex-icon-package-not-activated');
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
                    <tr class="rex-package-is-' . $type . $class . '">
                        <td><a href="' . rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '"><i class="rex-icon rex-icon-package-' . $type . '"></i></a></td>
                        <td data-title="' . rex_i18n::msg('package_hname') . '"><a href="' . rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '">' . $name . ' <i class="rex-icon rex-icon-help"></i></a></td>
                        <td data-pjax-container="#rex-page">' . $install . '</td>
                        <td data-pjax-container="#rex-page">' . $status . '</td>
                        <td data-pjax-container="#rex-page">' . $uninstall . '</td>
                        <td data-pjax-container="#rex-page">' . $delete . '</td>
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


    $fragment = new rex_fragment();
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
