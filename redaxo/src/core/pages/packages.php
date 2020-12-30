<?php

/**
 * @package redaxo5
 */

$subpage = rex_request('subpage', 'string');

function packages_title() {
    $package = rex_package::get(rex_request('package', 'string'));
    $subPage = rex_request('subpage', 'string');
    $packageId = $package->getPackageId();

    $navigation = [
        ['href' => rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]), 'title' => rex_i18n::msg('package_hhelp') . ' / ' . rex_i18n::msg('credits')],
        ['href' => rex_url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_license')],
        ['href' => rex_url::currentBackendPage(['subpage' => 'changelog', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_changelog')],
    ];

    switch($subPage) {
        case 'help':
            $navigation[0]['active'] = true;
            break;
        case 'license':
            $navigation[1]['active'] = true;
            break;
        case 'changelog':
            $navigation[2]['active'] = true;
            break;
    }

    $fragment = new rex_fragment();
    $fragment->setVar('left', $navigation, false);
    $subtitle = $fragment->parse('core/navigations/content.php');

    echo rex_view::title(rex_i18n::msg('addons'), $subtitle);
}

if ('changelog' == $subpage) {
    require __DIR__ .'/packages.changelog.php';
}

if ('help' == $subpage) {
    require __DIR__ .'/packages.help.php';
}

if ('license' == $subpage) {
    require __DIR__ .'/packages.license.php';
}

if ('' == $subpage) {
    require __DIR__ .'/packages.list.php';
}
