<?php

$addonkey = rex_request('addonkey', 'string');
$download = rex_request('download', 'string');

if($addonkey && $download && !rex_addon::exists($addonkey))
{
  $result = rex_install_packages::downloadAddon($addonkey, $download);
  if($result === true)
  {
    $addonkey = '';
    echo rex_info('Das Addon wurde erfolgreich heruntergeladen und im Addonverzeichnis enpackt!');
    $refresh = true;
  }
  else
  {
    echo rex_warning($result);
  }
}

if($addonkey)
{
  $addon = rex_install_packages::getAddon($addonkey);

  echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. $addon[0]['addon_name'] .'</h2>
  	<table class="rex-table">
  		<tr>
  			<th>Key</th>
  			<td>'. $addon[0]['addon_key'] .'</td>
  		</tr>
  		<tr>
  			<th>Name</th>
  			<td>'. $addon[0]['addon_name'] .'</td>
  		</tr>
  		<tr>
  			<th>Kurzbeschreibung</th>
  			<td>'. nl2br($addon[0]['addon_shortdescription']) .'</td>
  		</tr>
  		<tr>
  			<th>Beschreibung</th>
  			<td>'. nl2br($addon[0]['addon_description']) .'</td>
  		</tr>
  	</table>
  	<table class="rex-table">
  		<tr>
  			<th colspan="4">Dateien</th>
  		</tr>
  		<tr>
  			<th>Name</th>
  			<th>Version</th>
  			<th>Beschreibung</th>
  			<th></th>
  		</tr>';

  foreach($addon as $file)
  {
    echo '
      <tr>
      	<td>'. $file['file_name'] .'</td>
      	<td>'. $file['file_version'] .'</td>
      	<td>'. $file['file_description'] .'</td>
      	<td><a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;addonkey='. $addonkey .'&amp;download='. $file['file_path'] .'">Download</a></td>
      </tr>';
  }

  echo '
  	</table>
  </div>';

}
else
{

  $addons = rex_install_packages::getAddAddons();

  echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. count($addons) .' AddOns gefunden</h2>
  	<table class="rex-table">
  		<tr>
  			<th class="rex-icon"></th>
  			<th>Key</th>
  			<th>Name</th>
  			<th>Kurzbeschreibung</th>
  		</tr>';

  foreach($addons as $addon)
  {
    $a = '<a href="index.php?page=install&amp;subpage=packages&amp;subsubpage=add&amp;addonkey='. $addon['addon_key'] .'">';
    echo '
    	<tr>
    		<td class="rex-icon">'. $a .'<span class="rex-i-element rex-i-addon"><span class="rex-i-element-in">'. $addon['addon_key'] .'</span></span></a></td>
    		<td>'. $a . $addon['addon_key'] .'</a></td>
    		<td>'. $addon['addon_name'] .'</td>
    		<td>'. $addon['addon_shortdescription'] .'</td>
    	</tr>';
  }

  echo '
  	</table>
  </div>';

}