<?php

$content = '';

$settings = rex_post('settings', 'array', array());

if(!empty($settings))
{
  $keys = array('backups', 'api_login', 'api_key');
  foreach($keys as $key)
  {
    if(isset($settings[$key]))
      $this->setConfig($key, $settings[$key]);
  }
  $content .= rex_view::info($this->i18n('settings_saved'));
  rex_install_webservice::deleteCache();
}

$content .= '
  <div class="rex-form">
    <h2 class="rex-hl2">'. $this->i18n('settings') .'</h2>
    <form action="index.php?page=install&amp;subpage=settings" method="post">
      <fieldset class="rex-form-col-1">
        <legend>'. $this->i18n('settings_general') .'</legend>
        <div class="rex-form-wrapper">
          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input type="hidden" name="settings[backups]" value="0" />
              <input id="install-settings-backups" type="checkbox" class="rex-form-checkbox" name="settings[backups]" value="1" '. ($this->getConfig('backups') ? 'checked="checked" ' : '') .'/>
              <label for="install-settings-backups">'. $this->i18n('settings_backups') .'</label>
            </p>
          </div>
        </div>
      </fieldset>
      <fieldset class="rex-form-col-1">
        <legend>'. $this->i18n('settings_myredaxo_account') .'</legend>
        <div class="rex-form-wrapper">
          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="install-settings-api-login">'. $this->i18n('settings_api_login') .'</label>
              <input id="install-settings-api-login" class="rex-form-text" type="text" name="settings[api_login]" value="'. $this->getConfig('api_login') .'" />
            </p>
          </div>
          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="install-settings-api-key">'. $this->i18n('settings_api_key') .'</label>
              <input id="install-settings-api-key" class="rex-form-text" type="text" name="settings[api_key]" value="'. $this->getConfig('api_key') .'" />
            </p>
          </div>
          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-submit rex-form-submit-2">
              <input id="install-settings-save" type="submit" name="settings[save]" class="rex-form-submit" value="'. rex_i18n::msg('form_save') .'" />
            </p>
          </div>
        </div>
      </fieldset>
    </form>
  </div>';
  
echo rex_view::contentBlock($content);


