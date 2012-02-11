<?php

$addonkey = rex_request('addonkey', 'string');
$addons = array();

echo rex_api_function::getMessage();

try
{
  $addons = rex_install_packages::getUpdatePackages();
}
catch(rex_functional_exception $e)
{
  echo rex_view::warning($e->getMessage());
  $addonkey = '';
}

$content = '';

if($addonkey && isset($addons[$addonkey]))
{
  $addon = $addons[$addonkey];
  
  $content .= '
    <h2>'. $addonkey .'</h2>
    <table class="rex-table">
      <tbody>
      <tr>
        <th>'. $this->i18n('name') .'</th>
        <td>'. $addon['name'] .'</td>
      </tr>
      <tr>
        <th>'. $this->i18n('author') .'</th>
        <td>'. $addon['author'] .'</td>
      </tr>
      <tr>
        <th>'. $this->i18n('shortdescription') .'</th>
        <td>'. nl2br($addon['shortdescription']) .'</td>
      </tr>
      <tr>
        <th>'. $this->i18n('description') .'</th>
        <td>'. nl2br($addon['description']) .'</td>
      </tr>
      </tbody>
    </table>
    
    <h3>'. $this->i18n('files') .'</h3>
    <table class="rex-table">
      <thead>
      <tr>
        <th class="rex-icon"></th>
        <th class="rex-version">'. $this->i18n('version') .'</th>
        <th class="rex-description">'. $this->i18n('description') .'</th>
        <th class="rex-function"></th>
      </tr>
      </thead>
      <tbody>';

  foreach($addon['files'] as $fileId => $file)
  {
    $content .= '
      <tr>
        <td class="rex-icon"><span class="rex-ic-addon">'. $file['version'] .'</span></td>
        <td class="rex-version">'. $file['version'] .'</td>
        <td class="rex-description">'. nl2br($file['description']) .'</td>
        <td class="rex-update"><a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=&amp;addonkey='. $addonkey .'&amp;rex-api-call=install_packages_update&amp;file='. $fileId .'">'. $this->i18n('update') .'</a></td>
      </tr>';
  }

  $content .= '</tbody></table>';

}
else
{
  $content .= '
    <h2>'. $this->i18n('available_updates', count($addons)) .'</h2>

    <table class="rex-table">
      <thead>
      <tr>
        <th class="rex-icon"></th>
        <th class="key">'. $this->i18n('key') .'</th>
        <th class="name">'. $this->i18n('name') .'</th>
        <th class="version">'. $this->i18n('existing_version') .'</th>
        <th class="version">'. $this->i18n('available_versions') .'</th>
      </tr>
      </thead>
      <tbody>';

  foreach($addons as $key => $addon)
  {
    $availableVersions = array();
    foreach($addon['files'] as $file)
    {
      $availableVersions[] = $file['version'];
    }
    $a = '<a%s href="index.php?page=install&amp;subpage=packages&amp;subsubpage=&amp;addonkey='. $key .'">%s</a>';
    
    $content .= '
      <tr>
        <td class="rex-icon">'. sprintf($a, ' class="rex-ic-addon"', $key) .'</a></td>
        <td class="key">'. sprintf($a, '', $key) .'</a></td>
        <td class="name">'. $addon['name'] .'</td>
        <td class="version">'. rex_addon::get($key)->getVersion() .'</td>
        <td class="version">'. implode(', ', $availableVersions) .'</td>
      </tr>';
  }

  $content .= '</tbody></table>';
  
}

echo rex_view::contentBlock($content, '', 'block');
