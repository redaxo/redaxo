<?php

/** @var rex_addon $this */

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
                <th>' . $this->i18n('version') . '</th>
                <th>' . $this->i18n('description') . '</th>
                <th class="rex-table-action"></th>
            </tr>
            </thead>
            <tbody>';

    foreach ($coreVersions as $id => $version) {
        $panel .= '
                <tr>
                    <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                    <td data-title="' . $this->i18n('version') . '">' . $version['version'] . '</td>
                    <td data-title="' . $this->i18n('description') . '">' . nl2br($version['description']) . '</td>
                    <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['core' => 1, 'rex-api-call' => 'install_core_update', 'version_id' => $id]) . '" data-pjax="false">' . $this->i18n('update') . '</a></td>
                </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', 'REDAXO Core', false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');
} elseif ($addonkey && isset($addons[$addonkey])) {
    $addon = $addons[$addonkey];

    $panel = '
        <table class="table">
            <tbody>
            <tr>
                <th class="rex-table-width-5">' . $this->i18n('name') . '</th>
                <td data-title="' . $this->i18n('name') . '">' . $addon['name'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('author') . '</th>
                <td data-title="' . $this->i18n('author') . '">' . $addon['author'] . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('shortdescription') . '</th>
                <td data-title="' . $this->i18n('shortdescription') . '">' . nl2br($addon['shortdescription']) . '</td>
            </tr>
            <tr>
                <th>' . $this->i18n('description') . '</th>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($addon['description']) . '</td>
            </tr>
            </tbody>
        </table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', '<b>' . $addonkey . '</b> ' . $this->i18n('information'), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');

    $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"></th>
                <th class="rex-table-width-4">' . $this->i18n('version') . '</th>
                <th>' . $this->i18n('description') . '</th>
                <th class="rex-table-action"></th>
            </tr>
            </thead>
            <tbody>';

    foreach ($addon['files'] as $fileId => $file) {
        $panel .= '
            <tr>
                <td class="rex-table-icon"><i class="rex-icon rex-icon-package"></i></td>
                <td data-title="' . $this->i18n('version') . '">' . $file['version'] . '</td>
                <td data-title="' . $this->i18n('description') . '">' . nl2br($file['description']) . '</td>
                <td class="rex-table-action"><a href="' . rex_url::currentBackendPage(['addonkey' => $addonkey, 'rex-api-call' => 'install_package_update', 'file' => $fileId]) . '" data-pjax="false">' . $this->i18n('update') . '</a></td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('files'), false);
    $fragment->setVar('content', $panel, false);
    $content .= $fragment->parse('core/page/section.php');
} else {
    $panel = '
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th class="rex-table-icon"><a href="' . rex_url::currentBackendPage(['func' => 'reload']) . '" title="' . $this->i18n('reload') . '"><i class="rex-icon rex-icon-refresh"></i></a></th>
                <th>' . $this->i18n('key') . '</th>
                <th>' . $this->i18n('name') . '</th>
                <th>' . $this->i18n('existing_version') . '</th>
                <th>' . $this->i18n('available_versions') . '</th>
            </tr>
            </thead>
            <tbody>';

    if (!empty($coreVersions)) {
        $availableVersions = [];
        foreach ($coreVersions as $file) {
            $availableVersions[] = $file['version'];
        }
        $url = rex_url::currentBackendPage(['core' => 1]);

        $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $this->i18n('key') . '"><a href="' . $url . '">core</a></td>
                <td data-title="' . $this->i18n('name') . '">REDAXO Core</td>
                <td data-title="' . $this->i18n('existing_version') . '">' . rex::getVersion() . '</td>
                <td data-title="' . $this->i18n('available_versions') . '">' . implode(', ', $availableVersions) . '</td>
            </tr>';
    }

    foreach ($addons as $key => $addon) {
        $availableVersions = [];
        foreach ($addon['files'] as $file) {
            $availableVersions[] = $file['version'];
        }
        $url = rex_url::currentBackendPage(['addonkey' => $key]);

        $panel .= '
            <tr>
                <td class="rex-table-icon"><a href="' . $url . '"><i class="rex-icon rex-icon-package"></i></a></td>
                <td data-title="' . $this->i18n('key') . '"><a href="' . $url . '">' . $key . '</a></td>
                <td data-title="' . $this->i18n('name') . '">' . $addon['name'] . '</td>
                <td data-title="' . $this->i18n('existing_version') . '">' . rex_addon::get($key)->getVersion() . '</td>
                <td data-title="' . $this->i18n('available_versions') . '">' . implode(', ', $availableVersions) . '</td>
            </tr>';
    }

    $panel .= '</tbody></table>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('available_updates', !empty($coreVersions) + count($addons)), false);
    $fragment->setVar('content', $panel, false);
    $content = $fragment->parse('core/page/section.php');
}

echo $message;
echo $content;
