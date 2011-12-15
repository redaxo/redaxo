<?php

$addonkey = rex_request('addonkey', 'string');
$addons = array();

echo rex_api_function::getMessage();

try
{
  $addons = rex_install_packages::getMyPackages();
}
catch(rex_functional_exception $e)
{
  echo rex_view::warning($e->getMessage());
  $addonkey = '';
}

if($addonkey && isset($addons[$addonkey]))
{
  $addon = $addons[$addonkey];
  $file_id = rex_request('file', 'string');

  if($file_id)
  {
    $new = $file_id == 'new';
    $file = $new ? array('version' => '', 'description' => '', 'status' => 1) : $addon['files'][$file_id];

    $newVersion = rex_addon::get($addonkey)->getVersion();

    echo '
  <div class="rex-form">
  	<h2 class="rex-hl2">'. $addonkey .': '. $this->i18n($new ? 'file_add' : 'file_edit') .'</h2>
  	<form action="index.php?page=install&amp;subpage=packages&amp;subsubpage=upload&amp;rex-api-call=install_packages_upload&amp;addonkey='. $addonkey .'&amp;file='. $file_id .'" method="post">
			<fieldset class="rex-form-col-1">
				<div class="rex-form-wrapper">
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-read">
            	<label for="install-packages-upload-version">'. $this->i18n('version') .'</label>
          		<span id="install-packages-upload-version" class="rex-form-read">'. ($new ? $newVersion : $file['version']) .'</span>
            </p>
          </div>
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-textarea">
            	<label for="install-packages-upload-description">'. $this->i18n('description') .'</label>
          		<textarea id="install-packages-upload-description" class="rex-form-textarea" name="upload[description]" cols="50" rows="6">'. $file['description'] .'</textarea>
            </p>
          </div>
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
          		<input id="install-packages-upload-status" type="checkbox" class="rex-form-checkbox" name="upload[status]" value="1" '. (!$new && $file['status'] ? 'checked="checked" ' : '') .'/>
            	<label for="install-packages-upload-status">'. $this->i18n('online') .'</label>
            </p>
          </div>
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
          		<input id="install-packages-upload-upload-file" type="checkbox" class="rex-form-checkbox" name="upload[upload_file]" value="1" '. ($new ? 'checked="checked" disabled="disabled"' : '') .'/>
            	<label for="install-packages-upload-upload-file">'. $this->i18n('upload_file') .'</label>
            </p>
          </div>
  				<div class="rex-form-row">
     				<p class="rex-form-col-a rex-form-submit rex-form-submit-2">
     					<input id="install-packages-upload-send" type="submit" name="upload[send]" class="rex-form-submit" value="'. $this->i18n('send') .'" />
						</p>
          </div>
  			</div>
  		</fieldset>
  	</form>
  </div>';

    if(!$new && $newVersion != $file['version'])
    {
      echo '
  <script type="text/javascript"><!--

    jQuery(function($) {
			$("#install-packages-upload-upload-file").change(function(){
  			if($(this).is(":checked"))
  				$("#install-packages-upload-version").html("<span class=\'rex-strike\'>'. $file['version'] .'</span> <strong>'. $newVersion .'</strong>");
  			else
  				$("#install-packages-upload-version").html("'. $file['version'] .'");
  		});
    });

  //--></script>';
    }

  }
  else
  {

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
  		  <th class="rex-icon"><a class="rex-i-element rex-i-generic-add" href="index.php?page=install&amp;subpage=packages&amp;subsubpage=upload&amp;addonkey='. $addonkey .'&amp;file=new" title="'. $this->i18n('file_add') .'">'. $this->i18n('file_add') .'</a></th>
  			<th>'. $this->i18n('version') .'</th>
  			<th>'. $this->i18n('description') .'</th>
  			<th>'. $this->i18n('status') .'</th>
  		</tr>';

    foreach($addon['files'] as $fileId => $file)
    {
      $a = '<a%s href="index.php?page=install&amp;subpage=packages&amp;subsubpage=upload&amp;addonkey='. $addonkey .'&amp;file='. $fileId .'">%s</a>';
      $status = $file['status'] ? 'online' : 'offline';
      echo '
      <tr>
        <td class="rex-icon">'. sprintf($a, ' class="rex-i-element rex-i-addon"', '<span class="rex-i-element-text">'. $file['version'] .'</span>') .'</td>
      	<td>'. sprintf($a, '', $file['version']) .'</a></td>
      	<td>'. nl2br($file['description']) .'</td>
      	<td><span class="rex-'. $status .'">'. $this->i18n($status) .'</span></td>
      </tr>';
    }

    echo '
  	</table>
  </div>';

  }

}
else
{

  echo '
  <div class="rex-area">
  	<h2 class="rex-hl2">'. $this->i18n('my_packages') .'</h2>
  	<table class="rex-table">
  		<tr>
  			<th class="rex-icon"></th>
  			<th>'. $this->i18n('key') .'</th>
  			<th>'. $this->i18n('name') .'</th>
  			<th>'. $this->i18n('status') .'</th>
  		</tr>';

  foreach($addons as $key => $addon)
  {
    $a = '<a%s href="index.php?page=install&amp;subpage=packages&amp;subsubpage=upload&amp;addonkey='. $key .'">%s</a>';
    $status = $addon['status'] ? 'online' : 'offline';
    echo '
    	<tr>
    		<td class="rex-icon">'. sprintf($a, ' class="rex-i-element rex-i-addon"', '<span class="rex-i-element-text">'. $key .'</span>') .'</a></td>
    		<td>'. sprintf($a, '', $key) .'</a></td>
    		<td>'. $addon['name'] .'</td>
      	<td><span class="rex-'. $status .'">'. $this->i18n($status) .'</span></td>
    	</tr>';
  }

  echo '
  	</table>
  </div>
  ';

}