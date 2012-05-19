<?php

$content = '';

// CAST FORM PARAMS
////////////////////////////////////////////////////////////////////////////////
$settings = rex_post('settings', array(
    array('sql_log'           ,'int'   ,0),
    array('sql_slow_threshold','int'   ,5),
    array('ep_log'            ,'int'   ,0),
    array('ep_log_filter'     ,'string',''),
    array('api_log'           ,'int'   ,0),
    array('firephp_settings'  ,'string','default'),
    array('maxDepth'          ,'int'   ,10),
    array('maxArrayDepth'     ,'int'   ,5),
    array('maxObjectDepth'    ,'int'   ,5),
), null);

if (is_array($settings))
{
  foreach($settings as $key => $value)
  {
    $this->setConfig($key, $value);
  }
  $content .= rex_view::info($this->i18n('settings_saved'));
}


// FIREPHP SETTINGS MODE SELECT
////////////////////////////////////////////////////////////////////////////////
$key = 'firephp_settings';
$sel = new rex_select();
$sel->setSize(1);
$sel->setAttribute('id',$key);
$sel->setName('settings['.$key.']');
$sel->addOption('Default','default');
$sel->addOption('Custom','custom');
$sel->setSelected($this->getConfig($key,'default'));
$settings_select = $sel->get();


// maxDepth SELECT
////////////////////////////////////////////////////////////////////////////////
$key = 'maxDepth';
$sel = new rex_select();
$sel->setSize(1);
$sel->setName('settings['.$key.']');
for ($i=2; $i<15; $i++) // minimum level = 2, else logs won't show
{
  $default = $i==10 ? ' (default)' : '';
  $sel->addOption($i .' Level'. $default,$i);
}
$sel->setSelected($this->getConfig($key,10));
$maxDepth_select = $sel->get();


// maxArrayDepth SELECT
////////////////////////////////////////////////////////////////////////////////
$key = 'maxArrayDepth';
$sel = new rex_select();
$sel->setSize(1);
$sel->setName('settings['.$key.']');
for ($i=2; $i<15; $i++) // minimum level = 2, else logs won't show
{
  $default = $i==5 ? ' (default)' : '';
  $sel->addOption($i .' Level'. $default,$i);
}
$sel->setSelected($this->getConfig($key,5));
$maxArrayDepth_select = $sel->get();


// maxObjectDepth SELECT
////////////////////////////////////////////////////////////////////////////////
$key = 'maxObjectDepth';
$sel = new rex_select();
$sel->setSize(1);
$sel->setName('settings['.$key.']');
for ($i=2; $i<15; $i++) // minimum level = 2, else logs won't show
{
  $default = $i==5 ? ' (default)' : '';
  $sel->addOption($i .' Level'. $default,$i);
}
$sel->setSelected($this->getConfig($key,5));
$maxObjectDepth_select = $sel->get();


$custom_css = $this->getConfig('firephp_settings')!='custom' ? 'display:none' : '';

// PAGE OUTPUT
////////////////////////////////////////////////////////////////////////////////
$content .= '
  <div class="rex-form">
    <form action="index.php?page=debug&amp;subpage=settings" method="post">

      <fieldset class="rex-form-col-1">
        <h2 class="rex-hl2">'. $this->i18n('sql_log') .'</h2>
        <legend class="rex-hl2" style="display:none;">'. $this->i18n('sql_log') .'</legend>
        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input id="debug-sql-log" type="checkbox" class="rex-form-checkbox" name="settings[sql_log]" value="1" '. ($this->getConfig('sql_log') ? 'checked="checked" ' : '') .'/>
              <label for="debug-sql-log">'. $this->i18n('activate') .'</label>
            </p>
          </div><!-- /.rex-form-row -->

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="sql_slow_threshold">'. $this->i18n('sql_slow_threshold') .'</label>
              <input id="sql_slow_threshold" class="rex-form-text" type="text" name="settings[sql_slow_threshold]" value="'. ($this->getConfig('sql_slow_threshold')==0 ? 5 : $this->getConfig('sql_slow_threshold')) .'" />ms
            </p>
          </div><!-- /.rex-form-row -->

        </div><!-- /.rex-form-wrapper -->
      </fieldset>

      <fieldset class="rex-form-col-1">
        <h2 class="rex-hl2">'. $this->i18n('ep_log') .'</h2>
        <legend class="rex-hl2" style="display:none;">'. $this->i18n('ep_log') .'</legend>
        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input id="debug-ep-log" type="checkbox" class="rex-form-checkbox" name="settings[ep_log]" value="1" '. ($this->getConfig('ep_log') ? 'checked="checked" ' : '') .'/>
              <label for="debug-ep-log">'. $this->i18n('activate') .'</label>
            </p>
          </div><!-- /.rex-form-row -->

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-text">
              <label for="ep_log_filter">'. $this->i18n('ep_log_filter') .'</label>
              <input id="ep_log_filter" class="rex-form-text" type="text" name="settings[ep_log_filter]" value="'. $this->getConfig('ep_log_filter') .'" />
            </p>
          </div><!-- /.rex-form-row -->

        </div><!-- /.rex-form-wrapper -->
      </fieldset>

      <fieldset class="rex-form-col-1">
        <h2 class="rex-hl2">'. $this->i18n('api_log') .'</h2>
        <legend class="rex-hl2" style="display:none;">'. $this->i18n('api_log') .'</legend>
        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-checkbox rex-form-label-right">
              <input id="api_log" type="checkbox" class="rex-form-checkbox" name="settings[api_log]" value="1" '. ($this->getConfig('api_log') ? 'checked="checked" ' : '') .'/>
              <label for="api_log">'. $this->i18n('activate') .'</label>
            </p>
          </div><!-- /.rex-form-row -->

        </div><!-- /.rex-form-wrapper -->
      </fieldset>


      <fieldset class="rex-form-col-1">
        <h2>'. $this->i18n('firephp_settings') .'</h2>
        <legend style="display:none;">'. $this->i18n('firephp_settings') .'</legend>
        <div class="rex-form-wrapper">

          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-select">
              <label for="firephp_settings">'. $this->i18n('configuration') .'</label>
              '. $settings_select .'
            </p>
          </div><!-- /.rex-form-row -->


          <div id="firephp-custom" style="'.$custom_css.'">

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-select">
                <label for="maxDepth">maxDepth</label>
                '. $maxDepth_select .'
              </p>
            </div><!-- /.rex-form-row -->

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-select">
                <label for="maxArrayDepth">maxArrayDepth</label>
                '. $maxArrayDepth_select .'
              </p>
            </div><!-- /.rex-form-row -->

            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-select">
                <label for="maxObjectDepth">maxObjectDepth</label>
                '. $maxObjectDepth_select .'
              </p>
            </div><!-- /.rex-form-row -->

          </div><!-- /#firephp-custom -->


          <div class="rex-form-row">
            <p class="rex-form-col-a rex-form-submit rex-form-submit-2">
              <input id="install-settings-save" type="submit" name="settings[save]" class="rex-form-submit" value="'. rex_i18n::msg('form_save') .'" />
            </p>
          </div><!-- /.rex-form-row -->


        </div><!-- /.rex-form-wrapper -->
      </fieldset>

    </form>
  </div>

<script type="text/javascript">
<!--
jQuery(function($) {

  // toggle firephp custom settings block
  $("#firephp_settings").change(function() {
    $("#firephp-custom").toggle("fast");
  });

});
//-->
</script>

  ';

echo rex_view::contentBlock($content);
