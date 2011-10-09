<?php

/**
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$info = '';
$warning = '';

if ($func == 'setup')
{
  // REACTIVATE SETUP

  $configFile = rex_path::src('config.yml');
  $config = rex_file::getConfig($configFile);
  $config['setup'] = true;
  // echo nl2br(htmlspecialchars($cont));
  if (rex_file::putConfig($configFile, $config) !== false)
  {
    $info = rex_i18n::msg('setup_error1', '<a href="index.php">', '</a>');
  }
  else
  {
    $warning = rex_i18n::msg('setup_error2');
  }
}elseif ($func == 'generate')
{
  // generate all articles,cats,templates,caches
  $info = rex_generateAll();
}
elseif ($func == 'updateinfos')
{
  $configFile = rex_path::src('config.yml');
  $config = rex_file::getConfig($configFile);

  $settings = rex_post('settings', 'array', array());

  foreach(array('server', 'servername', 'error_email', 'lang') as $key)
  {
    if(isset($settings[$key]))
    {
      $config[$key] = $settings[$key];
      rex::setProperty($key, $settings[$key]);
    }
  }

  foreach(rex_system_setting::getAll() as $setting)
  {
    $key = $setting->getKey();
    if(isset($settings[$key]))
    {
      $value = $setting->cast($settings[$key]);
      if(($error = $setting->isValid($value)) !== true)
      {
        $warning .= $error .'<br />';
      }
      else
      {
        $config[$key] = $value;
        rex::setProperty($key, $value);
      }
    }
  }

  if($warning == '')
  {
    if(rex_file::putConfig($configFile, $config) > 0)
    {
      $info = rex_i18n::msg('info_updated');
    }
  }
}

$sel_lang = new rex_select();
$sel_lang->setStyle('class="rex-form-select"');
$sel_lang->setName('settings[lang]');
$sel_lang->setId('rex-form-lang');
$sel_lang->setSize(1);
$sel_lang->setSelected(rex::getProperty('lang'));

foreach (rex_i18n::getLocales() as $l)
{
  $sel_lang->addOption($l, $l);
}

if ($warning != '')
  echo rex_warning($warning);

if ($info != '')
  echo rex_info($info);

$dbconfig = rex::getProperty('db');



$version = rex_path::version();
if (strlen($version)>21)
	$version = substr($version,0,8)."..".substr($version,strlen($version)-13);


$headline_1 = rex_i18n::msg("system_features");
$headline_2 = rex_i18n::msg("system_settings");

$content_1 = '
						<h4 class="rex-hl3">'.rex_i18n::msg("delete_cache").'</h4>
						<p class="rex-tx1">'.rex_i18n::msg("delete_cache_description").'</p>
						<p class="rex-button"><a class="rex-button" href="index.php?page=system&amp;func=generate"><span><span>'.rex_i18n::msg("delete_cache").'</span></span></a></p>

						<h4 class="rex-hl3">'.rex_i18n::msg("setup").'</h4>
						<p class="rex-tx1">'.rex_i18n::msg("setup_text").'</p>
						<p class="rex-button"><a class="rex-button" href="index.php?page=system&amp;func=setup" onclick="return confirm(\''.rex_i18n::msg("setup").'?\');"><span><span>'.rex_i18n::msg("setup").'</span></span></a></p>

            <h4 class="rex-hl3">'.rex_i18n::msg("version").'</h4>
            <p class="rex-tx1">
            REDAXO: '.rex::getVersion().'<br />
            PHP: '.phpversion().' (<a href="index.php?page=system&amp;subpage=phpinfo" onclick="newWindow(\'phpinfo\', this.href, 800,600,\',status=yes,resizable=yes\');return false;">php_info</a>)</p>

            <h4 class="rex-hl3">'.rex_i18n::msg("database").'</h4>
            <p class="rex-tx1">MySQL: '.rex_sql::getServerVersion().'<br />'.rex_i18n::msg("name").': '.$dbconfig[1]['name'].'<br />'.rex_i18n::msg("host").': '.$dbconfig[1]['host'].'</p>';


$content_2 = '
						<fieldset class="rex-form-col-1">
							<legend>'. rex_i18n::msg("general_info_header").'</legend>

							<div class="rex-form-wrapper">

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-server">Server</label>
										<input class="rex-form-text" type="text" id="rex-form-server" name="settings[server]" value="'. htmlspecialchars(rex::getProperty('server')).'" />
									</p>
								</div>

								<div class="rex-form-row">
									<p class="rex-form-col-a rex-form-text">
										<label for="rex-form-servername">Servername</label>
										<input class="rex-form-text" type="text" id="rex-form-servername" name="settings[servername]" value="'. htmlspecialchars(rex::getProperty('servername')).'" />
									</p>
								</div>
							</div>

							<div class="rex-form-row">
								<p class="rex-form-col-a rex-form-read">
									<label for="rex_src_path">Path</label>
									<span class="rex-form-read" id="rex_src_path" title="'. rex_path::version() .'">&quot;'.$version.'&quot;</span>
								</p>
							</div>

							<div class="rex-form-row">
								<p class="rex-form-col-a rex-form-text">
									<label for="rex-form-error-email">Error email</label>
									<input class="rex-form-text" type="text" id="rex-form-error-email" name="settings[error_email]" value="'. htmlspecialchars(rex::getProperty('error_email')).'" />
								</p>
							</div>';

foreach(rex_system_setting::getAll() as $setting)
{
  $field = $setting->getField();
  if(!($field instanceof rex_form_element))
  {
    throw new rex_exception(get_class($setting) .'::getField() must return a rex_form_element!');
  }
  $field->setAttribute('name', 'settings['. $setting->getKey() .']');
  $field->setValue(rex::getProperty($setting->getKey()));
  $content_2 .= $field->get();
}

$content_2 .= '

							<div class="rex-form-row">
								<p class="rex-form-col-a rex-form-submit">
									<input type="submit" class="rex-form-submit" name="sendit" value="'. rex_i18n::msg("system_update").'" '. rex::getAccesskey(rex_i18n::msg('system_update'), 'save').' />
								</p>
							</div>

						</fieldset>';

?>

<div class="rex-form" id="rex-form-system-setup">
	<form action="index.php" method="post">
  	<input type="hidden" name="page" value="system" />
  	<input type="hidden" name="func" value="updateinfos" />

<?php
$fragment = new rex_fragment();
$fragment->setVar('headline_1', $headline_1, false);
$fragment->setVar('headline_2', $headline_2, false);
$fragment->setVar('content_1', $content_1, false);
$fragment->setVar('content_2', $content_2, false);
echo $fragment->parse('section_grid2col');
unset($fragment);
?>
	</form>
</div>