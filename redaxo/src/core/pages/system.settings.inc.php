<?php

/**
 *
 * @package redaxo5
 */

$info = '';
$warning = '';
$error = '';
$success = '';

if ($func == 'setup') {
  // REACTIVATE SETUP

  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);
  $config['setup'] = true;
  // echo nl2br(htmlspecialchars($cont));
  if (rex_file::putConfig($configFile, $config) !== false) {
    $info = rex_i18n::msg('setup_error1', '<a href="index.php" data-pjax="false">', '</a>');

    header('Location:' . rex_url::backendController());
    exit;

  } else {
    $warning = rex_i18n::msg('setup_error2');
  }
} elseif ($func == 'generate') {
  // generate all articles,cats,templates,caches
  $success = rex_deleteCache();
} elseif ($func == 'updateinfos') {
  $configFile = rex_path::data('config.yml');
  $config = rex_file::getConfig($configFile);

  $settings = rex_post('settings', 'array', array());

  foreach (array('server', 'servername', 'error_email', 'lang') as $key) {
    if (isset($settings[$key])) {
      $config[$key] = $settings[$key];
      rex::setProperty($key, $settings[$key]);
    }
  }

  $config['debug'] = isset($settings['debug']) && $settings['debug'];
  rex::setProperty('debug', $config['debug']);

  foreach (rex_system_setting::getAll() as $setting) {
    $key = $setting->getKey();
    if (isset($settings[$key])) {
      $value = $setting->cast($settings[$key]);
      if (($error = $setting->isValid($value)) !== true) {
        $warning .= $error . '<br />';
      } else {
        $config[$key] = $value;
        rex::setProperty($key, $value);
      }
    }
  }

  if (empty($config['error_email'])) {
    $warning = rex_i18n::msg('error_email_required');
  }

  if ($warning == '') {
    if (rex_file::putConfig($configFile, $config) > 0) {
      $success = rex_i18n::msg('info_updated');
    }
  }
}

$sel_lang = new rex_select();
$sel_lang->setStyle('class="rex-form-select"');
$sel_lang->setName('settings[lang]');
$sel_lang->setId('rex-form-lang');
$sel_lang->setSize(1);
$sel_lang->setSelected(rex::getProperty('lang'));

foreach (rex_i18n::getLocales() as $l) {
  $sel_lang->addOption($l, $l);
}

if ($warning != '')
  echo rex_view::warning($warning);

if ($info != '')
  echo rex_view::info($info);

if ($success != '')
  echo rex_view::success($success);

$dbconfig = rex::getProperty('db');



$version = rex_path::src();
if (strlen($version) > 21)
  $version = substr($version, 0, 8) . '..' . substr($version, strlen($version) - 13);

$content_1 = '<h2>' . rex_i18n::msg('system_features') . '</h2>
            <h3>' . rex_i18n::msg('delete_cache') . '</h3>
            <p>' . rex_i18n::msg('delete_cache_description') . '</p>
            <p class="rex-button"><a class="rex-button" href="index.php?page=system&amp;func=generate">' . rex_i18n::msg('delete_cache') . '</a></p>

            <h3>' . rex_i18n::msg('setup') . '</h3>
            <p>' . rex_i18n::msg('setup_text') . '</p>
            <p class="rex-button"><a class="rex-button" href="index.php?page=system&amp;func=setup" data-confirm="' . rex_i18n::msg('setup_restart') . '?" data-pjax="false">' . rex_i18n::msg('setup') . '</a></p>

            <h3>' . rex_i18n::msg('version') . '</h3>
            <p>
            REDAXO: ' . rex::getVersion() . '<br />
            PHP: ' . phpversion() . ' (<a href="index.php?page=system&amp;subpage=phpinfo" onclick="newWindow(\'phpinfo\', this.href, 800,600,\',status=yes,resizable=yes\');return false;">php_info</a>)</p>

            <h3>' . rex_i18n::msg('database') . '</h3>
            <p>MySQL: ' . rex_sql::getServerVersion() . '<br />' . rex_i18n::msg('name') . ': ' . $dbconfig[1]['name'] . '<br />' . rex_i18n::msg('host') . ': ' . $dbconfig[1]['host'] . '</p>';


$content_2 = '
        <h2>' . rex_i18n::msg('system_settings') . '</h2>
            <fieldset>
              <h3>' . rex_i18n::msg('general_info_header') . '</h3>';

          $formElements = array();

            $n = array();
            $n['label'] = '<label for="rex-form-server">' . rex_i18n::msg('server') . '</label>';
            $n['field'] = '<input type="text" id="rex-form-server" name="settings[server]" value="' . htmlspecialchars(rex::getProperty('server')) . '" />';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="rex-form-servername">' . rex_i18n::msg('servername') . '</label>';
            $n['field'] = '<input type="text" id="rex-form-servername" name="settings[servername]" value="' . htmlspecialchars(rex::getProperty('servername')) . '" />';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="rex-form-src-path">' . rex_i18n::msg('path') . '</label>';
            $n['field'] = '<span class="rex-form-read" id="rex-form-src-path" title="' . rex_path::src() . '">&quot;' . $version . '&quot;</span>';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="rex-form-error-email">' . rex_i18n::msg('error_email') . '</label>';
            $n['field'] = '<input type="text" id="rex-form-error-email" name="settings[error_email]" value="' . htmlspecialchars(rex::getProperty('error_email')) . '" />';
            $formElements[] = $n;

            $n = array();
            $n['label'] = '<label for="rex-form-debug">' . rex_i18n::msg('debug_mode') . '</label>';
            $n['field'] = '<input type="checkbox" id="rex-form-debug" name="settings[debug]" value="1" ' . (rex::isDebugMode() ? 'checked="checked" ' : '') . '/>';
            $n['reverse'] = true;
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          $content_2 .= $fragment->parse('form.tpl');


foreach (rex_system_setting::getAll() as $setting) {
  $field = $setting->getField();
  if (!($field instanceof rex_form_element)) {
    throw new rex_exception(get_class($setting) . '::getField() must return a rex_form_element!');
  }
  $field->setAttribute('name', 'settings[' . $setting->getKey() . ']');
  $field->setValue(rex::getProperty($setting->getKey()));
  $content_2 .= $field->get();
}
          $formElements = array();

            $n = array();
            $n['field'] = '<input type="submit" name="sendit" value="' . rex_i18n::msg('system_update') . '" ' . rex::getAccesskey(rex_i18n::msg('system_update'), 'save') . ' />';
            $formElements[] = $n;

          $fragment = new rex_fragment();
          $fragment->setVar('elements', $formElements, false);
          $content_2 .= $fragment->parse('form.tpl');

$content_2 .= '
            </fieldset>';

$content_2 = '
<div class="rex-form" id="rex-form-system-setup">
  <form action="index.php" method="post">
    <input type="hidden" name="page" value="system" />
    <input type="hidden" name="func" value="updateinfos" />' .
    $content_2 .
    '</form>
</div>';

echo rex_view::contentBlock($content_1, $content_2);
