<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\View;

$package = Addon::require(rex_request('package', 'string'));
$subPage = rex_request('subpage', 'string');
$packageId = $package->getPackageId();

$hasChangelog = is_readable($package->getPath('CHANGELOG.md'));

$navigation = [
    'help' => ['href' => Url::currentBackendPage(['subpage' => 'help', 'package' => $packageId]), 'title' => I18n::msg('package_hhelp') . ' / ' . I18n::msg('credits')],
    'changelog' => ['href' => Url::currentBackendPage(['subpage' => 'changelog', 'package' => $packageId]), 'title' => I18n::msg('credits_changelog')],
    'license' => ['href' => Url::currentBackendPage(['subpage' => 'license', 'package' => $packageId]), 'title' => I18n::msg('credits_license')],
];

if (!in_array($subPage, ['help', 'changelog', 'license'], true)) {
    throw new rex_exception('Unknown packages subpage "' . $subPage . '"');
}

$navigation[$subPage]['active'] = true;

if (!$hasChangelog) {
    unset($navigation['changelog']);
}

$fragment = new Fragment();
$fragment->setVar('left', $navigation, false);
$subtitle = $fragment->parse('core/navigations/content.php');

$headLine = 'AddOn: ' . $packageId;

echo View::title($headLine, $subtitle);

require __DIR__ . '/packages.' . $subPage . '.php';
