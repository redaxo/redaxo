<?php

$settings = rex_post('settings', 'array', array());

if(!empty($settings))
{
  $keys = array('username', 'apikey');
  foreach($keys as $key)
  {
    if(isset($settings[$key]))
      $this->setConfig($key, $settings[$key]);
  }
  echo rex_info($this->i18n('info_settings_saved'));
}

echo '
  <div class="rex-form">
  	<h2 class="rex-hl2">'. $this->i18n('subpage_settings') .'</h2>
  	<form action="index.php?page=install&amp;subpage=packages&amp;subsubpage=settings" method="post">
  		<fieldset class="rex-form-col-1">
  			<legend>'. $this->i18n('myredaxo_account') .'</legend>
  			<div class="rex-form-wrapper">
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
            	<label for="install-packages-settings-username">'. $this->i18n('username') .'</label>
          		<input id="install-packages-settings-username" class="rex-form-text" type="text" name="settings[username]" value="'. $this->getConfig('username') .'" />
            </p>
          </div>
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
            	<label for="install-packages-settings-apikey">'. $this->i18n('apikey') .'</label>
          		<input id="install-packages-settings-apikey" class="rex-form-text" type="text" name="settings[apikey]" value="'. $this->getConfig('apikey') .'" />
            </p>
          </div>
  				<div class="rex-form-row">
             <p class="rex-form-col-a">
         				<p class="rex-form-col-a rex-form-submit rex-form-submit-2">
         					<input id="install-packages-settings-save" type="submit" name="settings[save]" class="rex-form-submit" value="'. rex_i18n::msg('form_save') .'" />
								</p>
        			</p>
           </div>
  			</div>
  		</fieldset>
  	</form>
  </div>';