<?php

$content = '';

// CAST FORM PARAMS
////////////////////////////////////////////////////////////////////////////////
$settings = rex_post('settings', array(
    array('sql_log'         ,'int',0),
    array('ep_log'          ,'int',0),
    array('api_log'         ,'int',0),
    array('firephp_maxdepth','int',7)
), null);

if (is_array($settings))
{
  foreach($settings as $key => $value)
  {
    $this->setConfig($key, $value);
  }
  $content .= rex_view::info($this->i18n('settings_saved'));
}


// MAXDEPTH SELECT
////////////////////////////////////////////////////////////////////////////////
$tmp = new rex_select();
$tmp->setSize(1);
$tmp->setName('settings[firephp_maxdepth]');
for ($i=2; $i<15; $i++) // minimum level = 2, else logs won't show
{
  $tmp->addOption($i.' Level',$i);
}
$selected = $this->getConfig('firephp_maxdepth')==''
          ? 7
          : $this->getConfig('firephp_maxdepth');
$tmp->setSelected($selected);
$maxdepth_select = $tmp->get();


// PAGE OUTPUT
////////////////////////////////////////////////////////////////////////////////
$content .= '
  <div class="rex-form">
    <form action="index.php?page=debug&amp;subpage=settings" method="post">

      <fieldset class="rex-form-col-1">
        <legend class="rex-hl2">'. $this->i18n('debug_logs_activate') .'</legend>
        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input id="debug-sql-log" type="checkbox" class="rex-form-checkbox" name="settings[sql_log]" value="1" '. ($this->getConfig('sql_log') ? 'checked="checked" ' : '') .'/>
              <label for="debug-sql-log">'. $this->i18n('debug_logs_sql') .'</label>
            </p>
          </div><!-- /.rex-form-row -->

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input id="debug-ep-log" type="checkbox" class="rex-form-checkbox" name="settings[ep_log]" value="1" '. ($this->getConfig('ep_log') ? 'checked="checked" ' : '') .'/>
              <label for="debug-ep-log">'. $this->i18n('debug_logs_ep') .'</label>
            </p>
          </div><!-- /.rex-form-row -->

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input id="debug-api-log" type="checkbox" class="rex-form-checkbox" name="settings[api_log]" value="1" '. ($this->getConfig('api_log') ? 'checked="checked" ' : '') .'/>
              <label for="debug-api-log">'. $this->i18n('debug_logs_api') .'</label>
            </p>
          </div><!-- /.rex-form-row -->

        </div><!-- /.rex-form-wrapper -->
      </fieldset>


      <fieldset class="rex-form-col-1">
        <legend>'. $this->i18n('debug_firephp_settings') .'</legend>
        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-select">
              <label for="debug-firephp-maxdepth">'. $this->i18n('debug_firephp_maxdepth') .'</label>
              '.$maxdepth_select.'
            </p>
          </div><!-- /.rex-form-row -->

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-submit rex-form-submit-2">
              <input id="install-settings-save" type="submit" name="settings[save]" class="rex-form-submit" value="'. rex_i18n::msg('form_save') .'" />
            </p>
          </div><!-- /.rex-form-row -->


        </div><!-- /.rex-form-wrapper -->
      </fieldset>

    </form>
  </div>';

echo rex_view::contentBlock($content);


