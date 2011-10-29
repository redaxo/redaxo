<?php

$addons = rex_install_packages::getUpdateAddons();

$addonkey = rex_request('addonkey', 'string');

if($addonkey)
{
  $addon = $addons[$addonkey];

  echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. $addonkey .'</h2>
  	<table class="rex-table">
  		<tr>
  			<th>'. rex_i18n::msg('install_packages_name') .'</th>
  			<td>'. $addon['name'] .'</td>
  		</tr>
  		<tr>
  			<th>'. rex_i18n::msg('install_packages_author') .'</th>
  			<td>'. $addon['author'] .'</td>
  		</tr>
  		<tr>
  			<th>'. rex_i18n::msg('install_packages_shortdescription') .'</th>
  			<td>'. nl2br($addon['shortdescription']) .'</td>
  		</tr>
  		<tr>
  			<th>'. rex_i18n::msg('install_packages_description') .'</th>
  			<td>'. nl2br($addon['description']) .'</td>
  		</tr>
  	</table>
  	<table class="rex-table">
  		<tr>
  			<th colspan="4">'. rex_i18n::msg('install_packages_files') .'</th>
  		</tr>
  		<tr>
  			<th>'. rex_i18n::msg('install_packages_name') .'</th>
  			<th>'. rex_i18n::msg('install_packages_version') .'</th>
  			<th>'. rex_i18n::msg('install_packages_description') .'</th>
  			<th></th>
  		</tr>';

  foreach($addon['files'] as $file)
  {
    echo '
      <tr>
      	<td>'. $file['name'] .'</td>
      	<td>'. $file['version'] .'</td>
      	<td>'. $file['description'] .'</td>
      	<td><a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;addon='. $addonkey .'&amp;rex-api-call=install_packages_update&amp;file='. $file['filename'] .'">'. rex_i18n::msg('install_packages_update') .'</a></td>
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
  	<h2 class="rex-hl2">'. rex_i18n::msg('install_packages_available_updates', count($addons)) .'</h2>
  	<table class="rex-table">
  		<tr>
  			<th class="rex-icon"></th>
  			<th>'. rex_i18n::msg('install_packages_key') .'</th>
  			<th>'. rex_i18n::msg('install_packages_name') .'</th>
  			<th>'. rex_i18n::msg('install_packages_existing_version') .'</th>
  			<th>'. rex_i18n::msg('install_packages_available_version') .'</th>
  		</tr>';

  foreach($addons as $key => $addon)
  {
    $a = '<a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=&amp;addonkey='. $key .'">';
    echo '
    	<tr>
    		<td class="rex-icon">'. $a .'<span class="rex-i-element rex-i-addon"><span class="rex-i-element-in">'. $key .'</span></span></a></td>
    		<td>'. $a . $key .'</a></td>
    		<td>'. $addon['name'] .'</td>
    		<td>'. rex_addon::get($key)->getVersion() .'</td>
    		<td>'. implode(', ', $addon['available_versions']) .'</td>
    	</tr>';
  }

  echo '
  	</table>
  </div>
  ';

}