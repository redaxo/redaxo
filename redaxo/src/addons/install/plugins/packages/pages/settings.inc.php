<?php

$settings = rex_post('settings', 'array', array());

if(!empty($settings))
{
  $keys = array('api_login', 'api_key');
  foreach($keys as $key)
  {
    if(isset($settings[$key]))
      $this->setConfig($key, $settings[$key]);
  }
  echo rex_view::info($this->i18n('info_settings_saved'));
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
            	<label for="install-packages-settings-api-login">'. $this->i18n('username') .'</label>
          		<input id="install-packages-settings-api-login" class="rex-form-text" type="text" name="settings[api_login]" value="'. $this->getConfig('api_login') .'" />
            </p>
          </div>
  				<div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
            	<label for="install-packages-settings-api-key">'. $this->i18n('apikey') .'</label>
          		<input id="install-packages-settings-api-key" class="rex-form-text" type="text" name="settings[api_key]" value="'. $this->getConfig('api_key') .'" />
            </p>
          </div>
  				<div class="rex-form-row">
     				<p class="rex-form-col-a rex-form-submit rex-form-submit-2">
     					<input id="install-packages-settings-save" type="submit" name="settings[save]" class="rex-form-submit" value="'. rex_i18n::msg('form_save') .'" />
						</p>
          </div>
  			</div>
  		</fieldset>
  	</form>
  </div>';