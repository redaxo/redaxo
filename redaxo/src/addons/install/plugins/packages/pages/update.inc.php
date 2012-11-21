<?php

$addonkey = rex_request('addonkey', 'string');
$addons = array();

echo rex_api_function::getMessage();

try {
  $addons = rex_install_packages::getUpdatePackages();
} catch (rex_functional_exception $e) {
  echo rex_view::warning($e->getMessage());
  $addonkey = '';
}

$content = '';

if ($addonkey && isset($addons[$addonkey])) {
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
        <td class="rex-update"><a href="' . rex_url::currentBackendPage(array('addonkey' => $addonkey, 'rex-api-call' => 'install_packages_update', 'file' => $fileId)) . '">' . $this->i18n('update') . '</a></td>
      </tr>';
  }

  $content .= '</tbody></table>';

} else {
  $content .= '
    <h2>' . $this->i18n('available_updates', count($addons)) . '</h2>

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
