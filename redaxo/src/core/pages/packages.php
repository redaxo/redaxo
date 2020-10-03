<?php

/**
 * @package redaxo5
 */

echo rex_view::title(rex_i18n::msg('addons'), '');

$content = '';

// -------------- RequestVars
$subpage = rex_request('subpage', 'string');

// ----------------- HELPPAGE
if ('help' == $subpage) {
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
    } elseif (is_readable($package->getPath('README.'. rex_i18n::getLanguage() .'.md'))) {
        [$readmeToc, $readmeContent] = rex_markdown::factory()->parseWithToc(rex_file::get($package->getPath('README.'. rex_i18n::getLanguage() .'.md')));
        $fragment = new rex_fragment();
        $fragment->setVar('content', $readmeContent, false);
        $fragment->setVar('toc', $readmeToc, false);
        $content .= $fragment->parse('core/page/docs.php');
    } elseif (is_readable($package->getPath('README.md'))) {
        [$readmeToc, $readmeContent] = rex_markdown::factory()->parseWithToc(rex_file::get($package->getPath('README.md')));
        $fragment = new rex_fragment();
        $fragment->setVar('content', $readmeContent, false);
        $fragment->setVar('toc', $readmeToc, false);
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
    $credits .= '<dt>' . rex_i18n::msg('credits_name') . '</dt><dd>' . rex_escape($name) . '</dd>';

    if ($version) {
        $credits .= '<dt>' . rex_i18n::msg('credits_version') . '</dt><dd>' . $version . '</dd>';
    }
    if ($author) {
        $credits .= '<dt>' . rex_i18n::msg('credits_author') . '</dt><dd>' . rex_escape($author) . '</dd>';
    }
    if ($supportPage) {
        $credits .= '<dt>' . rex_i18n::msg('credits_supportpage') . '</dt><dd><a href="' . $supportPage . '" onclick="window.open(this.href); return false;">' . $supportPage . '</a></dd>';
    }

    $credits .= '</dl>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('credits'), false);
    $fragment->setVar('body', $credits, false);
    echo $fragment->parse('core/page/section.php');

    echo '<p><a class="btn btn-back" href="'.rex_url::backendPage('packages').'">' . rex_i18n::msg('package_back') . '</a></p>';
}

// ----------------- LICENSE page
if ('license' == $subpage) {
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
    echo '<a class="btn" rel="noopener noreferrer" target="_blank" href="https://choosealicense.com/licenses/">' . rex_i18n::msg('credits_explain_license') . '</a>';
}

// ----------------- OUT
if ('' == $subpage) {
    // the package manager don't know new packages in the addon folder
    // so we need to make them available
    rex_package_manager::synchronizeWithFileSystem();

    $toolbar = '
        <div class="form-group form-group-xs">
            <div class="input-group input-group-xs" id="rex-js-available-addon-search">
                <input class="form-control" type="text" placeholder="' . rex_i18n::msg('package_search') . '" '.(rex_request('function') ? '' : 'autofocus ').'/>
                <span class="input-group-btn"><button class="btn btn-default">' . rex_i18n::msg('package_clear') . '</button></span>
            </div>
        </div>
    ';

    $content .= '
            <table class="table table-hover" id="rex-js-table-available-packages-addons">
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

    $getLink = static function (rex_package $package, $function, $icon = '', $confirm = false, $key = null) {
        $onclick = '';
        if ($confirm) {
            $onclick = ' data-confirm="' . rex_i18n::msg($package->getType() . '_' . $function . '_question', $package->getName()) . '"';
        }
        $text = rex_i18n::msg('package_' . ($key ?: $function));
        $url = rex_url::currentBackendPage([
            'package' => $package->getPackageId(),
            'function' => $function,
        ] + rex_api_package::getUrlParams());

        $icon = ('' != $icon) ? '<i class="rex-icon ' . $icon . '"></i>' : '';
        $class = ($key ?: $function);
        return '<a href="' . $url . '"' . $onclick . '>' . $icon . ' ' . $text . '</a>';
    };

    $getTableRow = static function (rex_package $package) use ($getLink) {
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
        $name = '<span class="rex-' . $type . '-name">' . rex_escape($package->getName()) . '</span>';

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
        } elseif ($package->getPackageId() == rex_get('mark', 'string')) {
            $class = ' mark';
        }

        $version = '';
        if ('' !== trim($package->getVersion())) {
            $version = ' <span class="rex-' . $type . '-version">' . trim($package->getVersion()) . '</span>';

            if (rex_version::isUnstable($package->getVersion())) {
                $version = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $version;
            }
        }

        $license = '';
        if (is_readable($licenseFile = $package->getPath('LICENSE.md')) || is_readable($licenseFile = $package->getPath('LICENSE'))) {
            $f = fopen($licenseFile, 'r');
            $firstLine = fgets($f);
            fclose($f);

            if (preg_match('/^The MIT License(?: \(MIT\))$/i', $firstLine)) {
                $firstLine = 'MIT License';
            }

            $license = '<a href="'. rex_url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]) .'" data-pjax-scroll-to="0"><i class="rex-icon rex-icon-license"></i> '. rex_escape($firstLine) .'</a>';
        }

        return $message . '
                    <tr class="rex-package-is-' . $type . $class . '">
                        <td class="rex-table-icon"><i class="rex-icon rex-icon-package-' . $type . '"></i></td>
                        <td data-title="' . rex_i18n::msg('package_hname') . '">' . $name . '</td>
                        <td data-title="' . rex_i18n::msg('package_hversion') . '">' . $version . '</td>
                        <td class="rex-table-slim" data-title="' . rex_i18n::msg('package_hhelp') . '">
                            <a href="' . rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '" data-pjax-scroll-to="0" title="' . rex_i18n::msg('package_help') . ' ' . rex_escape($package->getName()) . '"><i class="rex-icon rex-icon-help"></i> ' . rex_i18n::msg('package_hhelp') . ' <span class="sr-only">' . rex_escape($package->getName()) . '</span></a>
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

    $content .= '
        <script type="text/javascript">
        <!--
        jQuery(function($) {
            var table = $("#rex-js-table-available-packages-addons");
            var tablebody = table.find("tbody");

            $("#rex-js-available-addon-search .form-control").keyup(function () {
                table.find("tr").show();
                var search = $(this).val().toLowerCase();
                if (search) {
                    table.find("tbody tr").each(function () {
                        var tr = $(this);
                        if (tr.text().toLowerCase().indexOf(search) < 0) {
							tr.hide();
                        }
                    });
                }
            });
            $("#rex-js-available-addon-search .btn").click(function () {
                $("#rex-js-available-addon-search .form-control").val("").trigger("keyup");
            });
        });
        //-->
        </script>
    ';

    $fragment = new rex_fragment();
    $fragment->setVar('title', rex_i18n::msg('package_caption'), false);
    $fragment->setVar('options', $toolbar, false);
    $fragment->setVar('content', $content, false);
    echo $fragment->parse('core/page/section.php');
}
