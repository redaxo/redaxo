<?php

$core = rex_request('core', 'boolean');
$addonkey = rex_request('addonkey', 'string');

$coreVersions = array();
$addons = array();

$content = rex_api_function::getMessage();

try {
  $coreVersions = rex_api_install_core_update::getVersions();
  $addons = rex_install_packages::getUpdatePackages();
} catch (rex_functional_exception $e) {
  $content .= rex_view::warning($e->getMessage());
  $addonkey = '';
}

if ($core && !empty($coreVersions)) {
  $content .= '
    <h2>REDAXO Core</h2>
    <table class="rex-table rex-install-core-versions">
      <thead>
      <tr>
        <th class="rex-icon"></th>
        <th class="rex-version">' . $this->i18n('version') . '</th>
        <th class="rex-description">' . $this->i18n('description') . '</th>
        <th class="rex-function"></th>
      </tr>
      </thead>
      <tbody>';

  foreach ($coreVersions as $id => $version) {
    $content .= '
        <tr>
          <td class="rex-icon"><span class="rex-ic-addon">' . $version['version'] . '</span></td>
          <td>' . $version['version'] . '</td>
          <td>' . nl2br($version['description']) . '</td>
          <td><a href="' . rex_url::currentBackendPage(array('core' => 1, 'rex-api-call' => 'install_core_update', 'version_id' => $id)) . '">' . $this->i18n('update') . '</a></td>
        </tr>';
  }

  $content .= '</tbody></table>';

} elseif ($addonkey && isset($addons[$addonkey])) {

  $addon = $addons[$addonkey];

  $content .= '
    <h2>' . $addonkey . '</h2>
    <table id="rex-install-packages-information" class="rex-table">
      <tbody>
      <tr>
        <th class="rex-term">' . $this->i18n('name') . '</th>
        <td class="rex-description">' . $addon['name'] . '</td>
      </tr>
      <tr>
        <th class="rex-term">' . $this->i18n('author') . '</th>
        <td class="rex-description">' . $addon['author'] . '</td>
      </tr>
      <tr>
        <th class="rex-term">' . $this->i18n('shortdescription') . '</th>
        <td class="rex-description">' . nl2br($addon['shortdescription']) . '</td>
      </tr>
      <tr>
        <th class="rex-term">' . $this->i18n('description') . '</th>
        <td class="rex-description">' . nl2br($addon['description']) . '</td>
      </tr>
      </tbody>
    </table>

    <h3>' . $this->i18n('files') . '</h3>
    <table class="rex-table rex-install-packages-files">
      <thead>
      <tr>
        <th class="rex-icon"></th>
        <th class="rex-version">' . $this->i18n('version') . '</th>
        <th class="rex-description">' . $this->i18n('description') . '</th>
        <th class="rex-function"></th>
      </tr>
      </thead>
      <tbody>';

  foreach ($addon['files'] as $fileId => $file) {
    $content .= '
      <tr>
        <td class="rex-icon"><span class="rex-ic-addon">' . $file['version'] . '</span></td>
        <td class="rex-version">' . $file['version'] . '</td>
        <td class="rex-description">' . nl2br($file['description']) . '</td>
        <td class="rex-update"><a href="' . rex_url::currentBackendPage(array('addonkey' => $addonkey, 'rex-api-call' => 'install_package_update', 'file' => $fileId)) . '">' . $this->i18n('update') . '</a></td>
      </tr>';
  }

  $content .= '</tbody></table>';

} else {
  $content .= '
    <h2>' . $this->i18n('available_updates', !empty($coreVersions) + count($addons)) . '</h2>

    <table id="rex-install-packages-addons" class="rex-table">
      <thead>
      <tr>
        <th class="rex-icon"></th>
        <th class="rex-key">' . $this->i18n('key') . '</th>
        <th class="rex-name">' . $this->i18n('name') . '</th>
        <th class="rex-version">' . $this->i18n('existing_version') . '</th>
        <th class="rex-version">' . $this->i18n('available_versions') . '</th>
      </tr>
      </thead>
      <tbody>';

  if (!empty($coreVersions)) {
    $availableVersions = array();
    foreach ($coreVersions as $file) {
      $availableVersions[] = $file['version'];
    }
    $url = rex_url::currentBackendPage(array('core' => 1));

    $content .= '
      <tr>
        <td class="rex-icon"><a class="rex-ic-addon" href="' . $url . '">core</a></td>
        <td class="rex-key"><a href="' . $url . '">core</a></td>
        <td class="rex-name">REDAXO Core</td>
        <td class="rex-version">' . rex::getVersion() . '</td>
        <td class="rex-version">' . implode(', ', $availableVersions) . '</td>
      </tr>';
  }

  foreach ($addons as $key => $addon) {
    $availableVersions = array();
    foreach ($addon['files'] as $file) {
      $availableVersions[] = $file['version'];
    }
    $url = rex_url::currentBackendPage(array('addonkey' => $key));

    $content .= '
      <tr>
        <td class="rex-icon"><a class="rex-ic-addon" href="' . $url . '">' . $key . '</a></td>
        <td class="rex-key"><a href="' . $url . '">' . $key . '</a></td>
        <td class="rex-name">' . $addon['name'] . '</td>
        <td class="rex-version">' . rex_addon::get($key)->getVersion() . '</td>
        <td class="rex-version">' . implode(', ', $availableVersions) . '</td>
      </tr>';
  }

  $content .= '</tbody></table>';

}

echo rex_view::contentBlock($content, '', 'block');
