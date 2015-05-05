<?php

/**
 *
 * @package redaxo5
 */

$info = '';
$error = [];
$success = '';

if ($func == 'setup') {
    // REACTIVATE SETUP

    $configFile = rex_path::data('config.yml');
    $config = rex_file::getConfig($configFile);
    $config['setup'] = true;
    // echo nl2br(htmlspecialchars($cont));
    if (rex_file::putConfig($configFile, $config) !== false) {
        $info = rex_i18n::msg('setup_error1', '<a href="' . rex_url::backendController() . '">', '</a>');

        header('Location:' . rex_url::backendController());
        exit;

    } else {
        $error[] = rex_i18n::msg('setup_error2');
    }
} elseif ($func == 'generate') {
    // generate all articles,cats,templates,caches
    $success = rex_delete_cache();
} elseif ($func == 'updateassets') {
    rex_dir::copy(rex_path::core('assets'), rex_path::assets());
    $success = 'Updated assets';
} elseif ($func == 'updateinfos') {
    $configFile = rex_path::data('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile)
    );

    $settings = rex_post('settings', 'array', []);

    foreach (['server', 'servername', 'error_email', 'lang'] as $key) {
        if (!isset($settings[$key]) || !$settings[$key]) {
            $error[] = rex_i18n::msg($key . '_required');
            continue;
        }
        $config[$key] = $settings[$key];
        try {
            rex::setProperty($key, $settings[$key]);
        } catch (InvalidArgumentException $e) {
            $error[] = rex_i18n::msg($key . '_invalid');
        }
    }

    $config['debug'] = isset($settings['debug']) && $settings['debug'];
    rex::setProperty('debug', $config['debug']);

    foreach (rex_system_setting::getAll() as $setting) {
        $key = $setting->getKey();
        if (isset($settings[$key])) {
            $value = $setting->cast($settings[$key]);
            if (($msg = $setting->isValid($value)) !== true) {
                $error[] = $msg;
            } else {
                $config[$key] = $value;
                rex::setProperty($key, $value);
            }
        }
    }

    if (empty($error)) {
        if (rex_file::putConfig($configFile, $config) > 0) {
            $success = rex_i18n::msg('info_updated');
        }
    }
}

$sel_lang = new rex_select();
$sel_lang->setStyle('class="form-control"');
$sel_lang->setName('settings[lang]');
$sel_lang->setId('rex-id-lang');
$sel_lang->setSize(1);
$sel_lang->setSelected(rex::getProperty('lang'));

foreach (rex_i18n::getLocales() as $l) {
    $sel_lang->addOption($l, $l);
}

if (!empty($error)) {
    echo rex_view::error(implode('<br />', $error));
}

if ($info != '') {
    echo rex_view::info($info);
}

if ($success != '') {
    echo rex_view::success($success);
}

$dbconfig = rex::getProperty('db');



$version = rex_path::src();
if (strlen($version) > 21) {
    $version = substr($version, 0, 8) . '..' . substr($version, strlen($version) - 13);
}
$content = [];
$content[] = '
                        <h3>' . rex_i18n::msg('delete_cache') . '</h3>
                        <p>' . rex_i18n::msg('delete_cache_description') . '</p>
                        <p><a class="btn btn-delete" href="' . rex_url::currentBackendPage(['func' => 'generate']) . '">' . rex_i18n::msg('delete_cache') . '</a></p>

                        <h3>' . rex_i18n::msg('setup') . '</h3>
                        <p>' . rex_i18n::msg('setup_text') . '</p>
                        <p><a class="btn btn-setup" href="' . rex_url::currentBackendPage(['func' => 'setup']) . '" data-confirm="' . rex_i18n::msg('setup_restart') . '?" data-pjax="false">' . rex_i18n::msg('setup') . '</a></p>';

$content[] = '
                        <h3>' . rex_i18n::msg('version') . '</h3>
                        <dl class="dl-horizontal">
                            <dt>REDAXO</dt><dd>' . rex::getVersion() . '</dd>
                            <dt>PHP</dt><dd>' . phpversion() . ' <a href="' . rex_url::backendPage('system/phpinfo') . '" title="phpinfo" onclick="newWindow(\'phpinfo\', this.href, 800,600,\',status=yes,resizable=yes\');return false;"><i class="rex-icon rex-icon-phpinfo"></i></a></dd>
                        </dl>

                        <h3>' . rex_i18n::msg('database') . '</h3>
                        <dl class="dl-horizontal">
                            <dt>MySQL</dt><dd>' . rex_sql::getServerVersion() . '</dd>
                            <dt>' . rex_i18n::msg('name') . '</dt><dd>' . $dbconfig[1]['name'] . '</dd>
                            <dt>' . rex_i18n::msg('host') . '</dt><dd>' . $dbconfig[1]['host'] . '</dd>
                        </dl>';


$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/grid.php');

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('system_features'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');




$content = [];

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-server">' . rex_i18n::msg('server') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-server" name="settings[server]" value="' . htmlspecialchars(rex::getServer()) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-servername">' . rex_i18n::msg('servername') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-servername" name="settings[servername]" value="' . htmlspecialchars(rex::getServerName()) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-error-email">' . rex_i18n::msg('error_email') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-error-email" name="settings[error_email]" value="' . htmlspecialchars(rex::getErrorEmail()) . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content[] = $fragment->parse('core/form/form.php');


$elements = '';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-lang">' . rex_i18n::msg('backend_language') . '</label>';
$n['field'] = $sel_lang->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$elements .= $fragment->parse('core/form/form.php');



$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-debug">' . rex_i18n::msg('debug_mode') . '</label>';
$n['field'] = '<input type="checkbox" id="rex-id-debug" name="settings[debug]" value="1" ' . (rex::isDebugMode() ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$elements .= $fragment->parse('core/form/checkbox.php');


foreach (rex_system_setting::getAll() as $setting) {
    $field = $setting->getField();
    if (!($field instanceof rex_form_element)) {
        throw new rex_exception(get_class($setting) . '::getField() must return a rex_form_element!');
    }
    $field->setAttribute('name', 'settings[' . $setting->getKey() . ']');
    $field->setValue(rex::getProperty($setting->getKey()));
    $elements .= $field->get();
}



$content[] = $elements;


$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/grid.php');



$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save" type="submit" name="sendit"' . rex::getAccesskey(rex_i18n::msg('system_update'), 'save') . '>' . rex_i18n::msg('system_update') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');



$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('system_settings'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
<form id="rex-form-system-setup" action="' . rex_url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="updateinfos" />
    ' . $content . '
</form>';

echo $content;
