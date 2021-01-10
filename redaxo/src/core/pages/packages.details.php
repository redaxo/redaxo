<?php

$package = rex_package::get(rex_request('package', 'string'));
$subPage = rex_request('subpage', 'string');
$packageId = $package->getPackageId();

$hasChangelog = is_readable($package->getPath('CHANGELOG.md'));

$navigation = [
    ['href' => rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]), 'title' => rex_i18n::msg('package_hhelp') . ' / ' . rex_i18n::msg('credits')],
    ['href' => rex_url::currentBackendPage(['subpage' => 'changelog', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_changelog')],
    ['href' => rex_url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_license')],
];

switch ($subPage) {
    case 'help':
        $navigation[0]['active'] = true;
        break;
    case 'changelog':
        $navigation[1]['active'] = true;
        break;
    case 'license':
        $navigation[2]['active'] = true;
        break;
    default:
        throw new rex_exception('Unknown packages subpage "'.$subPage.'"');
}

if (!$hasChangelog) {
    unset($navigation[1]);
}

$fragment = new rex_fragment();
$fragment->setVar('left', $navigation, false);
$subtitle = $fragment->parse('core/navigations/content.php');

if ($package instanceof rex_plugin_interface) {
    $headLine = 'PlugIn: '. $packageId;
} else {
    $headLine = 'AddOn: '. $packageId;
}

echo rex_view::title($headLine, $subtitle);

require __DIR__.'/packages.'.$subPage.'.php';
