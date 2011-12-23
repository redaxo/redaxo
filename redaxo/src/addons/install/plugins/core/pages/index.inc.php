<?php

$versions = array();

$apiFunc = rex_api_function::factory();
if($apiFunc && ($result = $apiFunc->getResult()) && $result->isSuccessfull())
{
  header('Location: index.php?page=install&subpage=core&info='. urlencode($result->getMessage()));
  exit;
}

if($info = rex_get('info', 'string'))
  echo rex_view::info($info);
else
  echo rex_api_function::getMessage();

try
{
  $versions = rex_api_install_core_update::getVersions();
}
catch(rex_functional_exception $e)
{
  echo rex_view::warning($e->getMessage());
}

echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. $this->i18n('available_updates', count($versions)) .'</h2>
  	<table class="rex-table">
  		<tr>
  			<th class="rex-icon"></th>
  			<th>'. $this->i18n('version') .'</th>
  			<th>'. $this->i18n('description') .'</th>
  			<th></th>
  		</tr>';

foreach($versions as $id => $version)
{
  $a = '<a%s href="index.php?page=install&amp;subpage=core&amp;version_id='. $id .'">%s</a>';
  echo '
    	<tr>
    		<td class="rex-icon"><span class="rex-i-element rex-i-addon"><span class="rex-i-element-text">'. $version['version'] .'</span></span></td>
    		<td>'. $version['version'] .'</td>
    		<td>'. nl2br($version['description']) .'</td>
    		<td><a href="index.php?page=install&amp;subpage=core&amp;rex-api-call=install_core_update&amp;version_id='. $id .'">'. $this->i18n('update') .'</a></td>
    	</tr>';
}

echo '
  	</table>
  </div>
  ';