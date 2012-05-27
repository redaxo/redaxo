<?php

$addonkey = rex_request('addonkey', 'string');
$addons = array();

echo rex_api_function::getMessage();

try
{
  $addons = rex_install_packages::getAddPackages();
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

    <h3>'. $this->i18n('information') .'</h3>
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
        <td class="rex-function"><a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;addonkey='. $addonkey .'&amp;rex-api-call=install_packages_add&amp;file='. $fileId .'">'. $this->i18n('download') .'</a></td>
      </tr>';
  }

  $content .= '</tbody></table>';

}
else
{

  $content .= '
    <h2>'. $this->i18n('addons_found', count($addons)) .'</h2>
    <table class="rex-table">
     <thead>
      <tr>
        <th class="rex-icon"></th>
        <th class="rex-key">'. $this->i18n('key') .'</th>
        <th class="rex-name">'. $this->i18n('name') .'</th>
        <th class="rex-author">'. $this->i18n('author') .'</th>
        <th class="rex-description">'. $this->i18n('shortdescription') .'</th>
      </tr>
     </thead>
     <tbody>';

  foreach($addons as $key => $addon)
  {
    $a = '<a%s href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;addonkey='. $key .'">%s</a>';
    $content .= '
      <tr>
        <td class="rex-icon">'. sprintf($a, ' class="rex-ic-addon"', $key) .'</a></td>
        <td class="rex-key">'. sprintf($a, '', $key) .'</a></td>
        <td class="rex-name">'. $addon['name'] .'</td>
        <td class="rex-author">'. $addon['author'] .'</td>
        <td class="rex-description">'. nl2br($addon['shortdescription']) .'</td>
      </tr>';
  }

  $content .= '</tbody></table>';

}

echo rex_view::contentBlock($content, '', 'block');
