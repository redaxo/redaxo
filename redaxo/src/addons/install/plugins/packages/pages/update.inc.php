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

if($addonkey && isset($addons[$addonkey]))
{
  $addon = $addons[$addonkey];

  echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. $addonkey .'</h2>
  	<table class="rex-table">
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
  	</table>
  	<table class="rex-table">
  		<tr>
  			<th colspan="4">'. $this->i18n('files') .'</th>
  		</tr>
  		<tr>
  			<th class="rex-icon"></th>
  			<th>'. $this->i18n('version') .'</th>
  			<th>'. $this->i18n('description') .'</th>
  			<th></th>
  		</tr>';

  foreach($addon['files'] as $fileId => $file)
  {
    echo '
      <tr>
        <td class="rex-icon"><span class="rex-i-element rex-i-addon"><span class="rex-i-element-in">'. $file['version'] .'</span></span></td>
      	<td>'. $file['version'] .'</td>
      	<td>'. nl2br($file['description']) .'</td>
      	<td><a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=&amp;addonkey='. $addonkey .'&amp;rex-api-call=install_packages_update&amp;file='. $fileId .'">'. $this->i18n('update') .'</a></td>
      </tr>';
  }

  echo '
  	</table>
  </div>';

}
else
{

  echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. $this->i18n('available_updates', count($addons)) .'</h2>
  	<table class="rex-table">
  		<tr>
  			<th class="rex-icon"></th>
  			<th>'. $this->i18n('key') .'</th>
  			<th>'. $this->i18n('name') .'</th>
  			<th>'. $this->i18n('existing_version') .'</th>
  			<th>'. $this->i18n('available_versions') .'</th>
  		</tr>';

  foreach($addons as $key => $addon)
  {
    $availableVersions = array();
    foreach($addon['files'] as $file)
    {
      $availableVersions[] = $file['version'];
    }
    $a = '<a%s href="index.php?page=install&amp;subpage=packages&amp;subsubpage=&amp;addonkey='. $key .'">%s</a>';
    echo '
    	<tr>
    		<td class="rex-icon">'. sprintf($a, ' class="rex-i-element rex-i-addon"', '<span class="rex-i-element-text">'. $key .'</span>') .'</a></td>
    		<td>'. sprintf($a, '', $key) .'</a></td>
    		<td>'. $addon['name'] .'</td>
    		<td>'. rex_addon::get($key)->getVersion() .'</td>
    		<td>'. implode(', ', $availableVersions) .'</td>
    	</tr>';
  }

  echo '
  	</table>
  </div>
  ';

}