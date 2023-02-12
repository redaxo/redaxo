<?php

$package = rex_package::get(rex_request('package', 'string'));
$subPage = rex_request('subpage', 'string');
$packageId = $package->getPackageId();

$hasChangelog = is_readable($package->getPath('CHANGELOG.md'));

$navigation = [
    'help' => ['href' => rex_url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]), 'title' => rex_i18n::msg('package_hhelp') . ' / ' . rex_i18n::msg('credits')],
    'changelog' => ['href' => rex_url::currentBackendPage(['subpage' => 'changelog', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_changelog')],
    'license' => ['href' => rex_url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]), 'title' => rex_i18n::msg('credits_license')],
];

if (!in_array($subPage, ['help', 'changelog', 'license'], true)) {
    throw new rex_exception('Unknown packages subpage "'.$subPage.'"');
}

$navigation[$subPage]['active'] = true;

if (!$hasChangelog) {
    unset($navigation['changelog']);
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
