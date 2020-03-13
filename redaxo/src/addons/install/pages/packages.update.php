<?php

assert(isset($markdown) && is_callable($markdown));

$package = rex_addon::get('install');

$core = rex_request('core', 'boolean');
$addonkey = rex_request('addonkey', 'string');

$coreVersions = [];
$addons = [];

$message = rex_api_function::getMessage();

try {
    $coreVersions = rex_api_install_core_update::getVersions();
    $addons = rex_install_packages::getUpdatePackages();
} catch (rex_functional_exception $e) {
    $message .= rex_view::warning($e->getMessage());
    $addonkey = '';
}

if ($core && !empty($coreVersions)) {
    $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon">&nbsp;</th>
                <th>' . $package->i18n('version') . '</th>
                <th>' . $package->i18n('description') . '</th>
                <th class="rex-table-action"></th>
            </tr>
            </thead>
            <tbody>';

    foreach ($coreVersions as $id => $file) {
        $version = rex_escape($file['version']);
        $description = $markdown($file['description']);

        if (class_exists(rex_version::class) && rex_version::isUnstable($version)) {
            $version = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $version;
            $description = rex_view::warning(rex_i18n::msg('unstable_version')) . $description;
        }

        $panel .= '
                <tr data-pjax-scroll-to="0">
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                    <td data-title="' . $package->i18n('version') . '">' . $version . '</td>
                    <td data-title="' . $package->i18n('description') . '">' . $description . '</td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['core' => 1, 'version_id' => $id] + rex_api_install_core_update::getUrlParams()) . '" data-pjax="false">' . $package->i18n('update') . '</a></td>
                </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', 'REDAXO Core', false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');
} elseif ($addonkey && isset($addons[$addonkey])) {
    $addon = $addons[$addonkey];

    $version = rex_escape(rex_addon::get($addonkey)->getVersion());
    if (class_exists(rex_version::class) && rex_version::isUnstable($version)) {
        $version = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $version;
    }

    $panel = '
        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $package->i18n('name') . '</th>
                <td data-title="' . $package->i18n('name') . '">' . rex_escape($addon['name']) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('existing_version') . '</th>
                <td data-title="' . $package->i18n('existing_version') . '">' . $version . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('author') . '</th>
                <td data-title="' . $package->i18n('author') . '">' . rex_escape($addon['author']) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('shortdescription') . '</th>
                <td data-title="' . $package->i18n('shortdescription') . '">' . nl2br(rex_escape($addon['shortdescription'])) . '</td>
            </tr>
            <tr>
                <th>' . $package->i18n('description') . '</th>
                <td data-title="' . $package->i18n('description') . '">' . nl2br(rex_escape($addon['description'])) . '</td>
            </tr>
            </tbody>
        </table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . $addonkey . '</b> ' . $package->i18n('information'), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');

    $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"></th>
                <th class="rex-table-width-4">' . $package->i18n('version') . '</th>
                <th>' . $package->i18n('description') . '</th>
                <th class="rex-table-action"></th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $version = rex_escape($file['version']);
        $description = $markdown($file['description']);

        if (class_exists(rex_version::class) && rex_version::isUnstable($version)) {
            $version = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $version;
            $description = rex_view::warning(rex_i18n::msg('unstable_version')) . $description;
        }

        $panel .= '
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                <td data-title="' . $package->i18n('version') . '">' . $version . '</td>
                <td data-title="' . $package->i18n('description') . '">' . $description . '</td>
                <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'file' => $fileId] + rex_api_install_package_update::getUrlParams()) . '" data-pjax="false">' . $package->i18n('update') . '</a></td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $package->i18n('files'), false);
    $fragment->setVar('content', $panel, false);
    $content .= $fragment->parse('core/page/section.php');
} else {
    $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $package->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $package->i18n('key') . '</th>
                <th>' . $package->i18n('name') . '</th>
                <th>' . $package->i18n('existing_version') . '</th>
                <th>' . $package->i18n('available_versions') . '</th>
            </tr>
            </thead>
            <tbody>';

    if (!empty($coreVersions)) {
        $availableVersions = [];
        foreach ($coreVersions as $file) {
            $availVers = rex_escape($file['version']);
            if (class_exists(rex_version::class) && rex_version::isUnstable($availVers)) {
                $availVers = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $availVers;
            }
            $availableVersions[] = $availVers;
        }
        $url = rex_url::currentBackendPage(['core' => 1]);

        $coreVersion = rex_escape(rex::getVersion());
        if (class_exists(rex_version::class) && rex_version::isUnstable($coreVersion)) {
            $coreVersion = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $coreVersion;
        }

        $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $package->i18n('key') . '"><a href="' . $url . '">core</a></td>
                <td data-title="' . $package->i18n('name') . '">REDAXO Core</td>
                <td data-title="' . $package->i18n('existing_version') . '">' . $coreVersion . '</td>
                <td data-title="' . $package->i18n('available_versions') . '">' . implode(', ', $availableVersions) . '</td>
            </tr>';
    }

    foreach ($addons as $key => $addon) {
        $availableVersions = [];
        foreach ($addon['files'] as $file) {
            $availVers = rex_escape($file['version']);
            if (class_exists(rex_version::class) && rex_version::isUnstable($availVers)) {
                $availVers = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $availVers;
            }
            $availableVersions[] = $availVers;
        }
        $url = rex_url::currentBackendPage(['addonkey' => $key]);

        $packageVersion = rex_escape(rex_addon::get($key)->getVersion());
        if (class_exists(rex_version::class) && rex_version::isUnstable($packageVersion)) {
            $packageVersion = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. $packageVersion;
        }

        $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $package->i18n('key') . '"><a href="' . $url . '">' . rex_escape($key) . '</a></td>
                <td data-title="' . $package->i18n('name') . '">' . rex_escape($addon['name']) . '</td>
                <td data-title="' . $package->i18n('existing_version') . '">' . $packageVersion . '</td>
                <td data-title="' . $package->i18n('available_versions') . '">' . implode(', ', $availableVersions) . '</td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $package->i18n('available_updates', !empty($coreVersions) + count($addons)), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');
}

echo $message;
echo $content;
