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
    $fragment->setVar('title', rex_i18n::msg('package_help') . ' ' . $name, false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');

    




    $credits = '';
    $credits .= '<dl class="dl-horizontal">';
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
    $fragment->setVar('title', rex_i18n::msg('credits'), false);
    $fragment->setVar('body', $credits, false);
    echo $fragment->parse('core/page/section.php');


    echo '<a class="btn btn-primary" href="javascript:history.back();"><i class="rex-icon rex-icon-back"></i> ' . rex_i18n::msg('package_back') . '</a>';

}

// ----------------- OUT
if ($subpage == '') {
    rex_package_manager::synchronizeWithFileSystem();

    $content .= '
            <table class="table table-hover">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th>' . rex_i18n::msg('package_hname') . '</th>
                    <th>' . rex_i18n::msg('package_hversion') . '</th>
                    <th>' . rex_i18n::msg('package_hhelp') . '</th>
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
        return '<a href="' . $url . '"' . $onclick . '>' . $icon . ' ' . $text . '</a>';
    };

    $getTableRow = function (rex_package $package) use ($getLink) {
        $packageId = $package->getPackageId();
        $type = $package->getType();

        $delete = $package->isSystemPackage() ? '<small class="text-muted">' . rex_i18n::msg($type . '_system' . $type) . '</small>' : $getLink($package, 'delete', 'rex-icon-package-delete', true);

        $uninstall = '&nbsp;';
        if ($package->isInstalled()) {
            $install = $getLink($package, 'install', 'rex-icon-package-is-installed', false, 'reinstall');
            $uninstall = $getLink($package, 'uninstall', 'rex-icon-package-uninstall', true);
        } else {
            $install = $getLink($package, 'install', 'rex-icon-package-not-installed');
            //$uninstall = rex_i18n::msg('package_notinstalled');
        }
        
        $class = '';
        $status = '&nbsp;';
        if ($package->isActivated()) {
            $status = $getLink($package, 'deactivate', 'rex-icon-package-is-activated');
            $class .= ' rex-package-is-activated';
        } elseif ($package->isInstalled()) {
            $status = $getLink($package, 'activate', 'rex-icon-package-not-activated');
            $class .= ' rex-package-is-installed';
        } else {
            $class .= ' rex-package-not-installed';
        }
        $name = '<span class="rex-' . $type . '-name">' . htmlspecialchars($package->getName()) . '</span>';
        
        $class .= $package->isSystemPackage() ? ' rex-system-' . $type : '';

        // --------------------------------------------- API MESSAGES
        $message = '';
        if ($package->getPackageId() == rex_get('package', 'string') && rex_api_function::hasMessage()) {
            $message = '
                    <tr class="rex-package-message">
                        <td colspan="8">
                             ' . rex_api_function::getMessage() . '
                        </td>
                    </tr>';
            $class = ' mark';
        }

        $version = (trim($package->getVersion()) != '') ? ' <span class="rex-' . $type . '-version">' . trim($package->getVersion()) . '</span>' : '';

        return $message . '
                    <tr class="rex-package-is-' . $type . $class . '">
                        <td><i class="rex-icon rex-icon-package-' . $type . '"></i></td>
                        <td data-title="' . rex_i18n::msg('package_hname') . '">' . $name . '</td>
                        <td data-title="' . rex_i18n::msg('package_hversion') . '">' . $version . '</td>
                        <td data-title="' . rex_i18n::msg('package_hhelp') . '"><a href="' . rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '" title="' . rex_i18n::msg('package_help') . ' ' . htmlspecialchars($package->getName()) . '"><i class="rex-icon rex-icon-help"></i> <span class="sr-only">' . rex_i18n::msg('package_help') . ' ' . htmlspecialchars($package->getName()) . '</span></a></td>
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
    $fragment->setVar('title', rex_i18n::msg('package_caption'), false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
