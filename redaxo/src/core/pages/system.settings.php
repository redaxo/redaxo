<?php
/**
 * @package redaxo5
 */

$error = [];
$success = '';

$func = rex_request('func', 'string');

$csrfToken = rex_csrf_token::factory('system');

if ($func && !$csrfToken->isValid()) {
    $error[] = rex_i18n::msg('csrf_token_invalid');
} elseif ('setup' == $func) {
    // REACTIVATE SETUP

    $configFile = rex_path::coreData('config.yml');
    $config = rex_file::getConfig($configFile);
    $config['setup'] = true;

    if (false !== rex_file::putConfig($configFile, $config)) {
        header('Location:' . rex_url::backendController());
        exit;
    }
    $error[] = rex_i18n::msg('setup_error2');
} elseif ('generate' == $func) {
    // generate all articles,cats,templates,caches
    $success = rex_delete_cache();
} elseif ('updateassets' == $func) {
    rex_dir::copy(rex_path::core('assets'), rex_path::coreAssets());
    $success = 'Updated assets';
} elseif ('debugmode' == $func) {
    $configFile = rex_path::coreData('config.yml');
    $config = array_merge(
        rex_file::getConfig(rex_path::core('default.config.yml')),
        rex_file::getConfig($configFile)
    );

    if (!is_array($config['debug'])) {
        $config['debug'] = [];
    }

    $config['debug']['enabled'] = (rex::isDebugMode()) ? false : true;
    rex::setProperty('debug', $config['debug']);
    if (rex_file::putConfig($configFile, $config) > 0) {
        $success = (rex::isDebugMode()) ? rex_i18n::msg('debug_mode_info_on') : rex_i18n::msg('debug_mode_info_off');
    }
} elseif ('updateinfos' == $func) {
    $configFile = rex_path::coreData('config.yml');
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

    if (empty($settings['editor'])) {
        $settings['editor'] = null;
    }
    $config['editor'] = $settings['editor'];
    rex::setProperty('editor', $config['editor']);

    foreach (rex_system_setting::getAll() as $setting) {
        $key = $setting->getKey();
        if (isset($settings[$key])) {
            if (true !== ($msg = $setting->setValue($settings[$key]))) {
                $error[] = $msg;
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
$sel_lang->setAttribute('class', 'form-control selectpicker');
$sel_lang->setSize(1);
$sel_lang->setSelected(rex::getProperty('lang'));
$locales = rex_i18n::getLocales();
asort($locales);
foreach ($locales as $locale) {
    $sel_lang->addOption(rex_i18n::msgInLocale('lang', $locale).' ('.$locale.')', $locale);
}

$sel_editor = new rex_select();
$sel_editor->setStyle('class="form-control"');
$sel_editor->setName('settings[editor]');
$sel_editor->setId('rex-id-editor');
$sel_editor->setAttribute('class', 'form-control selectpicker');
$sel_editor->setSize(1);
$sel_editor->setSelected(rex::getProperty('editor'));
$sel_editor->addArrayOptions(['' => rex_i18n::msg('system_editor_no_editor')] + rex_editor::factory()->getSupportedEditors());

if (!empty($error)) {
    echo rex_view::error(implode('<br />', $error));
}

if ('' != $success) {
    echo rex_view::success($success);
}

$dbconfig = rex::getProperty('db');

$rexVersion = rex::getVersion();
if (false !== strpos($rexVersion, '-dev')) {
    $hash = rex_version::gitHash(rex_path::base(), 'redaxo/redaxo');
    if ($hash) {
        $rexVersion .= '#'. $hash;
    }
}

if (rex_version::isUnstable($rexVersion)) {
    $rexVersion = '<i class="rex-icon rex-icon-unstable-version" title="'. rex_i18n::msg('unstable_version') .'"></i> '. rex_escape($rexVersion);
}

$mainContent = [];
$sideContent = [];
$debugConfirm = '';

if (!rex::isDebugMode()) {
    $debugConfirm = ' data-confirm="' . rex_i18n::msg('debug_confirm') . '" ';
}

$content = '
    <h3>' . rex_i18n::msg('delete_cache') . '</h3>
    <p>' . rex_i18n::msg('delete_cache_description') . '</p>
    <p><a class="btn btn-delete" href="' . rex_url::currentBackendPage(['func' => 'generate'] + $csrfToken->getUrlParams()) . '">' . rex_i18n::msg('delete_cache') . '</a></p>

    <h3>' . rex_i18n::msg('debug_mode') . '</h3>
    <p>' . rex_i18n::msg('debug_mode_note') . '</p>
    <p><a class="btn btn-debug-mode" href="' . rex_url::currentBackendPage(['func' => 'debugmode'] + $csrfToken->getUrlParams()) . '" data-pjax="false"'.$debugConfirm.'><i class="rex-icon rex-icon-heartbeat"></i> ' . (rex::isDebugMode() ? rex_i18n::msg('debug_mode_off') : rex_i18n::msg('debug_mode_on')) . '</a></p>

    <h3>' . rex_i18n::msg('safemode') . '</h3>
    <p>' . rex_i18n::msg('safemode_text') . '</p>';

$safemodeUrl = rex_url::currentBackendPage(['safemode' => '1'] + $csrfToken->getUrlParams());
if (rex::isSafeMode()) {
    $safemodeUrl = rex_url::currentBackendPage(['safemode' => '0'] + $csrfToken->getUrlParams());
}

$content .= '
    <p><a class="btn btn-safemode-activate" href="' . $safemodeUrl . '" data-pjax="false">' . (rex::isSafeMode() ? rex_i18n::msg('safemode_deactivate') : rex_i18n::msg('safemode_activate')) . '</a></p>


    <h3>' . rex_i18n::msg('setup') . '</h3>
    <p>' . rex_i18n::msg('setup_text') . '</p>
    <p><a class="btn btn-setup" href="' . rex_url::currentBackendPage(['func' => 'setup'] + $csrfToken->getUrlParams()) . '" data-confirm="' . rex_i18n::msg('setup_restart') . '?" data-pjax="false">' . rex_i18n::msg('setup') . '</a></p>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('system_features'));
$fragment->setVar('body', $content, false);
$sideContent[] = $fragment->parse('core/page/section.php');

$content = '
    <table class="table">
        <tr>
            <th class="rex-table-width-3">REDAXO</th>
            <td>' . $rexVersion . '</td>
        </tr>
        <tr>
            <th>PHP</th>
            <td>' . PHP_VERSION . ' <a href="' . rex_url::backendPage('system/phpinfo') . '" title="phpinfo" onclick="newWindow(\'phpinfo\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;"><i class="rex-icon rex-icon-phpinfo"></i></a></td>
        </tr>
    </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('version'));
$fragment->setVar('content', $content, false);
$sideContent[] = $fragment->parse('core/page/section.php');

$sql = rex_sql::factory();

$content = '
    <table class="table">
        <tr>
            <th class="rex-table-width-3">' . rex_i18n::msg('version') . '</th>
            <td>' .  $sql->getDbType().' '.$sql->getDbVersion() . '</td>
        </tr>
        <tr>
            <th>' . rex_i18n::msg('name') . '</th>
            <td><span class="rex-word-break">' . $dbconfig[1]['name'] . '</span></td>
        </tr>
        <tr>
            <th>' . rex_i18n::msg('host') . '</th>
            <td>' . $dbconfig[1]['host'] . '</td>
        </tr>
    </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('database'));
$fragment->setVar('content', $content, false);
$sideContent[] = $fragment->parse('core/page/section.php');

$content = '';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-server">' . rex_i18n::msg('server') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-server" name="settings[server]" value="' . rex_escape(rex::getServer()) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-servername">' . rex_i18n::msg('servername') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-servername" name="settings[servername]" value="' . rex_escape(rex::getServerName()) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-lang">' . rex_i18n::msg('backend_language') . '</label>';
$n['field'] = $sel_lang->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-error-email">' . rex_i18n::msg('error_email') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-error-email" name="settings[error_email]" value="' . rex_escape(rex::getErrorEmail()) . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

foreach (rex_system_setting::getAll() as $setting) {
    $field = $setting->getField();
    if (!($field instanceof rex_form_element)) {
        throw new rex_exception(get_class($setting) . '::getField() must return a rex_form_element!');
    }
    $field->setAttribute('name', 'settings[' . $setting->getKey() . ']');
    $content .= $field->get();
}

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-editor">' . rex_i18n::msg('system_editor') . '</label>';
$n['field'] = $sel_editor->get();
$n['note'] = rex_i18n::msg('system_editor_note');
$formElements[] = $n;

$configYml = rex_path::coreData('config.yml');
if ($url = rex_editor::factory()->getUrl($configYml, 0)) {
    $n = [];
    $n['label'] = '';
    $n['field'] = $n['field'] = '<a class="btn btn-sm btn-primary" href="'. $url .'">' . rex_i18n::msg('system_editor_open_file', basename($configYml)) . '</a>';
    $n['note'] = rex_i18n::msg('system_edit_config_note');
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="sendit"' . rex::getAccesskey(rex_i18n::msg('system_update'), 'save') . '>' . rex_i18n::msg('system_update') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('system_settings'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$mainContent[] = '
<form id="rex-form-system-setup" action="' . rex_url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="updateinfos" />
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>';

$fragment = new rex_fragment();
$fragment->setVar('content', [implode('', $mainContent), implode('', $sideContent)], false);
$fragment->setVar('classes', ['col-lg-8', 'col-lg-4'], false);
echo $fragment->parse('core/page/grid.php');
