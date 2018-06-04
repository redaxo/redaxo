<?php

/**
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('addons'), '');

$content = '';

// -------------- RequestVars
$subpage = rex_request('subpage', 'string');

// ----------------- HELPPAGE
if ($subpage == 'help') {
    $package = rex_package::get(rex_request('package', 'string'));
    $name = $package->getPackageId();
    $version = $package->getVersion();
    $author = $package->getAuthor();
    $supportPage = $package->getSupportPage();

    if (is_readable($package->getPath('help.php'))) {
        if (!$package->isAvailable() && is_readable($package->getPath('lang'))) {
            rex_i18n::addDirectory($package->getPath('lang'));
        }
        ob_start();
        $package->includeFile('help.php');
        $content .= ob_get_clean();
    } elseif (is_readable($package->getPath('README.md'))) {
        $fragment = new rex_fragment();
        $fragment->setVar('content', rex_markdown::factory()->parse(rex_file::get($package->getPath('README.md'))), false);
        $content .= $fragment->parse('core/page/docs.php');
    } else {
        $content .= rex_view::info(rex_i18n::msg('package_no_help_file'));
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
        $credits .= '<dt>' . rex_i18n::msg('credits_supportpage') . '</dt><dd><a href="' . $supportPage . '" onclick="window.open(this.href); return false;">' . $supportPage . '</a></dd>';
    }

    $credits .= '</dl>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('credits'), false);
    $fragment->setVar('body', $credits, false);
    echo $fragment->parse('core/page/section.php');

    echo '<a class="btn btn-back" href="javascript:history.back();">' . rex_i18n::msg('package_back') . '</a>';
}

// ----------------- LICENSE page
if ($subpage == 'license') {
    $package = rex_package::get(rex_request('package', 'string'));

    $license = null;
    if (is_readable($package->getPath('LICENSE.md'))) {
        $license = rex_markdown::factory()->parse(rex_file::get($package->getPath('LICENSE.md')));
    } elseif (is_readable($package->getPath('LICENSE'))) {
        $license = nl2br(file_get_contents($package->getPath('LICENSE')));
    }

    if ($license) {
        $fragment = new rex_fragment();
        $fragment->setVar('title', rex_i18n::msg('credits_license').': '.$package->getPackageId());
        $fragment->setVar('body', $license, false);
        echo '<div id="license"></div>'; // scroll anchor
        echo $fragment->parse('core/page/section.php');
    }

    echo '<a class="btn btn-back" href="javascript:history.back();">' . rex_i18n::msg('package_back') . '</a>';
}

// ----------------- OUT
if ($subpage == '') {
    rex_package_manager::synchronizeWithFileSystem();

    $content .= '
            <table class="table table-hover">
            <thead>
                <tr>
                    <th class="rex-table-icon">&nbsp;</th>
                    <th>' . rex_i18n::msg('package_hname') . '</th>
                    <th class="rex-table-slim">' . rex_i18n::msg('package_hversion') . '</th>
                    <th colspan="2">' . rex_i18n::msg('package_hinformation') . '</th>
                    <th class="rex-table-action">' . rex_i18n::msg('package_hinstall') . '</th>
                    <th class="rex-table-action">' . rex_i18n::msg('package_hactive') . '</th>
                    <th class="rex-table-action" colspan="2">' . rex_i18n::msg('package_hdelete') . '</th>
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
            'function' => $function,
        ] + rex_api_package::getUrlParams());

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
        if ($package->isAvailable()) {
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
                        <td colspan="9">
                             ' . rex_api_function::getMessage() . '
                        </td>
                    </tr>';
            $class = ' mark';
        }

        $version = (trim($package->getVersion()) != '') ? ' <span class="rex-' . $type . '-version">' . trim($package->getVersion()) . '</span>' : '';

        $license = '';
        if (is_readable($licenseFile = $package->getPath('LICENSE.md')) || is_readable($licenseFile = $package->getPath('LICENSE'))) {
            $f = fopen($licenseFile, 'r');
            $firstLine = fgets($f);
            fclose($f);

            if (preg_match('/^The MIT License(?: \(MIT\))$/i', $firstLine)) {
                $firstLine = 'MIT License';
            }

            $license = '<a href="'. rex_url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]) .'"><i class="rex-icon rex-icon-license"></i> '. rex_escape($firstLine) .'</a>';
        }

        return $message . '
                    <tr class="rex-package-is-' . $type . $class . '">
                        <td class="rex-table-icon"><i class="rex-icon rex-icon-package-' . $type . '"></i></td>
                        <td data-title="' . rex_i18n::msg('package_hname') . '">' . $name . '</td>
                        <td data-title="' . rex_i18n::msg('package_hversion') . '">' . $version . '</td>
                        <td class="rex-table-slim" data-title="' . rex_i18n::msg('package_hhelp') . '">
                            <a href="' . rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '" title="' . rex_i18n::msg('package_help') . ' ' . htmlspecialchars($package->getName()) . '"><i class="rex-icon rex-icon-help"></i> ' . rex_i18n::msg('package_hhelp') . ' <span class="sr-only">' . htmlspecialchars($package->getName()) . '</span></a>
                        </td>
                        <td class="rex-table-width-6" data-title="' . rex_i18n::msg('package_hlicense') . '">'. $license .'</td>
                        <td class="rex-table-action" data-pjax-container="#rex-js-page-container">' . $install . '</td>
                        <td class="rex-table-action" data-pjax-container="#rex-js-page-container">' . $status . '</td>
                        <td class="rex-table-action" data-pjax-container="#rex-js-page-container">' . $uninstall . '</td>
                        <td class="rex-table-action" data-pjax-container="#rex-js-page-container">' . $delete . '</td>
                    </tr>' . "\n   ";
    };

    foreach (rex_addon::getRegisteredAddons() as $addonName => $addon) {
        $content .= $getTableRow($addon);

        if ($addon->isAvailable()) {
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
