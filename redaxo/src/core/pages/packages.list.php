<?php

use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Str;
use Redaxo\Core\Util\Version;

echo rex_view::title(I18n::msg('addons'), '');

// the package manager don't know new packages in the addon folder
// so we need to make them available
rex_addon_manager::synchronizeWithFileSystem();

$fragment = new rex_fragment();
$fragment->setVar('id', 'rex-js-available-addon-search');
$fragment->setVar('autofocus', !rex_request('function', 'bool'));
$toolbar = $fragment->parse('core/form/search.php');

$content = '
        <table class="table table-hover rex-targeted-rows" id="rex-js-table-available-packages-addons">
        <thead>
            <tr>
                <th class="rex-table-icon">&nbsp;</th>
                <th>' . I18n::msg('package_hname') . '</th>
                <th class="rex-table-slim">' . I18n::msg('package_hversion') . '</th>
                <th colspan="2">' . I18n::msg('package_hinformation') . '</th>
                <th class="rex-table-action">' . I18n::msg('package_hinstall') . '</th>
                <th class="rex-table-action">' . I18n::msg('package_hactive') . '</th>
                <th class="rex-table-action" colspan="2">' . I18n::msg('package_hdelete') . '</th>
            </tr>
        </thead>
        <tbody>';

$getLink = static function (rex_addon $package, $function, $icon = '', $confirm = false, $key = null) {
    $onclick = '';
    if ($confirm) {
        $onclick = ' data-confirm="' . I18n::msg('addon_' . $function . '_question', $package->getName()) . '"';
    }
    $text = I18n::msg('package_' . ($key ?: $function));
    $url = Url::currentBackendPage([
        'package' => $package->getPackageId(),
        'function' => $function,
    ] + rex_api_package::getUrlParams());

    $icon = ('' != $icon) ? '<i class="rex-icon ' . $icon . '"></i>' : '';
    return '<a class="rex-link-expanded" href="' . $url . '"' . $onclick . ' data-pjax="false">' . $icon . ' ' . $text . '</a>';
};

$getTableRow = static function (rex_addon $package) use ($getLink) {
    $packageId = $package->getPackageId();

    $delete = $package->isSystemPackage() ? '<small class="text-muted">' . I18n::msg('addon_systemaddon') . '</small>' : $getLink($package, 'delete', 'rex-icon-package-delete', true);

    $uninstall = '&nbsp;';
    if ($package->isInstalled()) {
        $install = $getLink($package, 'install', 'rex-icon-package-is-installed', false, 'reinstall');
        $uninstall = $getLink($package, 'uninstall', 'rex-icon-package-uninstall', true);
    } else {
        $install = $getLink($package, 'install', 'rex-icon-package-not-installed');
        // $uninstall = I18n::msg('package_notinstalled');
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
    $name = '<span class="rex-addon-name">' . rex_escape($package->getName()) . '</span>';

    $class .= $package->isSystemPackage() ? ' rex-system-addon' : '';

    // --------------------------------------------- API MESSAGES
    if (($package->getPackageId() == rex_get('package', 'string') && rex_api_function::hasMessage()) || ($package->getPackageId() == rex_get('mark', 'string'))) {
        $class = ' mark';
    }

    $version = '';
    if ('' !== trim($package->getVersion())) {
        $version = ' <span class="rex-addon-version">' . trim($package->getVersion()) . '</span>';

        if (Version::isUnstable($package->getVersion())) {
            $version = '<i class="rex-icon rex-icon-unstable-version" title="' . I18n::msg('unstable_version') . '"></i> ' . $version;
        }
    }

    $license = '';
    if (is_readable($licenseFile = $package->getPath('LICENSE.md')) || is_readable($licenseFile = $package->getPath('LICENSE'))) {
        $f = fopen($licenseFile, 'r');
        $firstLine = fgets($f) ?: '';
        fclose($f);

        if (preg_match('/^The MIT License(?: \(MIT\))$/i', $firstLine)) {
            $firstLine = 'MIT License';
        }

        $license = '<a class="rex-link-expanded" href="' . Url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]) . '" data-pjax-scroll-to="0"><i class="rex-icon rex-icon-license"></i> ' . rex_escape($firstLine) . '</a>';
    }

    return '
                <tr id="package-' . rex_escape(Str::normalize($packageId, '-', '_')) . '" class="rex-package-is-addon' . $class . '">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-package-addon"></i></td>
                    <td data-title="' . I18n::msg('package_hname') . '">' . $name . '</td>
                    <td data-title="' . I18n::msg('package_hversion') . '">' . $version . '</td>
                    <td class="rex-table-slim" data-title="' . I18n::msg('package_hhelp') . '">
                        <a class="rex-link-expanded" href="' . Url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]) . '" data-pjax-scroll-to="0" title="' . I18n::msg('package_help') . ' ' . rex_escape($package->getName()) . '"><i class="rex-icon rex-icon-help"></i> ' . I18n::msg('package_hhelp') . ' <span class="sr-only">' . rex_escape($package->getName()) . '</span></a>
                    </td>
                    <td class="rex-table-width-6" data-title="' . I18n::msg('package_hlicense') . '">' . $license . '</td>
                    <td class="rex-table-action">' . $install . '</td>
                    <td class="rex-table-action">' . $status . '</td>
                    <td class="rex-table-action">' . $uninstall . '</td>
                    <td class="rex-table-action">' . $delete . '</td>
                </tr>' . "\n   ";
};

foreach (rex_addon::getRegisteredAddons() as $addon) {
    $content .= $getTableRow($addon);
}

$content .= '</tbody>
        </table>';

$content .= '
    <script type="text/javascript" nonce="' . rex_response::getNonce() . '">
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
    });
    rex_searchfield_init("#rex-js-available-addon-search");
    //-->
    </script>
';

echo rex_api_function::getMessage();

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('package_caption'), false);
$fragment->setVar('options', $toolbar, false);
$fragment->setVar('content', $content, false);
echo $fragment->parse('core/page/section.php');
