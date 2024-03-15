<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Form\Field\BaseField;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Editor;
use Redaxo\Core\Util\Version;

$error = [];
$success = '';

$func = rex_request('func', 'string');

$csrfToken = rex_csrf_token::factory('system');

if (rex_request('rex_debug_updated', 'bool', false)) {
    $success = (Core::isDebugMode()) ? I18n::msg('debug_mode_info_on') : I18n::msg('debug_mode_info_off');
}

if ($func && !$csrfToken->isValid()) {
    $error[] = I18n::msg('csrf_token_invalid');
} elseif ('setup' == $func && !Core::isLiveMode()) {
    // REACTIVATE SETUP
    if (false !== $url = rex_setup::startWithToken()) {
        header('Location:' . $url);
        exit;
    }
    $error[] = I18n::msg('setup_error2');
} elseif ('generate' == $func) {
    // generate all articles,cats,templates,caches
    $success = rex_delete_cache();
} elseif ('updateassets' == $func && !Core::isLiveMode()) {
    Dir::copy(Path::core('assets'), Path::coreAssets());

    $files = require Path::core('vendor_files.php');
    foreach ($files as $source => $destination) {
        File::copy(Path::core('assets_files/' . $source), Path::coreAssets($destination));
    }

    $success = 'Updated assets';
} elseif ('debugmode' == $func && !Core::isLiveMode()) {
    $configFile = Path::coreData('config.yml');
    $config = array_merge(
        File::getConfig(Path::core('default.config.yml')),
        File::getConfig($configFile),
    );

    if (!is_array($config['debug'])) {
        $config['debug'] = [];
    }

    $config['debug']['enabled'] = !Core::isDebugMode();
    Core::setProperty('debug', $config['debug']);
    if (File::putConfig($configFile, $config) > 0) {
        // reload the page so that debug mode is immediately visible
        rex_response::sendRedirect(Url::currentBackendPage(['rex_debug_updated' => true]));
    }
} elseif ('updateinfos' == $func) {
    $configFile = Path::coreData('config.yml');
    $config = array_merge(
        File::getConfig(Path::core('default.config.yml')),
        File::getConfig($configFile),
    );

    $settings = rex_post('settings', 'array', []);

    foreach (['server', 'servername', 'error_email', 'lang'] as $key) {
        if (!isset($settings[$key]) || !$settings[$key]) {
            $error[] = I18n::msg($key . '_required');
            continue;
        }
        $config[$key] = $settings[$key];
        try {
            Core::setProperty($key, $settings[$key]);
        } catch (InvalidArgumentException) {
            $error[] = I18n::msg($key . '_invalid');
        }
    }

    foreach (rex_system_setting::getAll() as $setting) {
        $key = $setting->getKey();
        if (isset($settings[$key])) {
            if (true !== ($msg = $setting->setValue($settings[$key]))) {
                $error[] = $msg;
            }
        }
    }

    if (empty($error)) {
        if (File::putConfig($configFile, $config) > 0) {
            $success = I18n::msg('info_updated');
        }
    }
} elseif ('update_editor' === $func) {
    $editor = rex_post('editor', [
        ['name', 'string', null],
        ['basepath', 'string', null],
        ['update_cookie', 'bool', false],
        ['delete_cookie', 'bool', false],
    ]);

    $editor['name'] = $editor['name'] ?: null;
    $editor['basepath'] = $editor['basepath'] ?: null;

    $cookieOptions = ['samesite' => 'strict'];

    if ($editor['delete_cookie']) {
        rex_response::clearCookie('editor', $cookieOptions);
        rex_response::clearCookie('editor_basepath', $cookieOptions);
        unset($_COOKIE['editor']);
        unset($_COOKIE['editor_basepath']);

        $success = I18n::msg('system_editor_success_cookie_deleted');
    } elseif ($editor['update_cookie']) {
        rex_response::sendCookie('editor', $editor['name'], $cookieOptions);
        rex_response::sendCookie('editor_basepath', $editor['basepath'], $cookieOptions);
        $_COOKIE['editor'] = $editor['name'];
        $_COOKIE['editor_basepath'] = $editor['basepath'];

        $success = I18n::msg('system_editor_success_cookie');
    } else {
        $configFile = Path::coreData('config.yml');
        $config = File::getConfig($configFile);

        $config['editor'] = $editor['name'];
        $config['editor_basepath'] = $editor['basepath'];
        Core::setProperty('editor', $config['editor']);
        Core::setProperty('editor_basepath', $config['editor_basepath']);

        File::putConfig($configFile, $config);
        $success = I18n::msg('system_editor_success_configyml');
    }
}

$selLang = new rex_select();
$selLang->setStyle('class="form-control"');
$selLang->setName('settings[lang]');
$selLang->setId('rex-id-lang');
$selLang->setAttribute('class', 'form-control selectpicker');
$selLang->setSize(1);
$selLang->setSelected(Core::getProperty('lang'));
$locales = I18n::getLocales();
asort($locales);
foreach ($locales as $locale) {
    $selLang->addOption(I18n::msgInLocale('lang', $locale) . ' (' . $locale . ')', $locale);
}

if (!empty($error)) {
    echo rex_view::error(implode('<br />', $error));
}

if ('' != $success) {
    echo rex_view::success($success);
}

$dbconfig = Core::getDbConfig(1);

$rexVersion = Core::getVersion();
if (str_contains($rexVersion, '-dev')) {
    $hash = Version::gitHash(Path::base(), 'redaxo/redaxo');
    if ($hash) {
        $rexVersion .= '#' . $hash;
    }
}

if (Version::isUnstable($rexVersion)) {
    $rexVersion = '<i class="rex-icon rex-icon-unstable-version" title="' . I18n::msg('unstable_version') . '"></i> ' . rex_escape($rexVersion);
}

$mainContent = [];
$sideContent = [];
$debugConfirm = '';

if (!Core::isDebugMode()) {
    $debugConfirm = ' data-confirm="' . I18n::msg('debug_confirm') . '" ';
}

$content = '
    <h3>' . I18n::msg('delete_cache') . '</h3>
    <p>' . I18n::msg('delete_cache_description') . '</p>
    <p><a class="btn btn-delete" href="' . Url::currentBackendPage(['func' => 'generate'] + $csrfToken->getUrlParams()) . '">' . I18n::msg('delete_cache') . '</a></p>';

if (!Core::isLiveMode()) {
    $content .= '
        <h3>' . I18n::msg('debug_mode') . '</h3>
        <p>' . I18n::msg('debug_mode_note') . '</p>
        <p><a class="btn btn-debug-mode" href="' . Url::currentBackendPage(['func' => 'debugmode'] + $csrfToken->getUrlParams()) . '" data-pjax="false"' . $debugConfirm . '><i class="rex-icon rex-icon-heartbeat"></i> ' . (Core::isDebugMode() ? I18n::msg('debug_mode_off') : I18n::msg('debug_mode_on')) . '</a></p>

        <h3>' . I18n::msg('safemode') . '</h3>
        <p>' . I18n::msg('safemode_text') . '</p>';

    $safemodeUrl = Url::currentBackendPage(['safemode' => '1'] + $csrfToken->getUrlParams());
    if (Core::isSafeMode()) {
        $safemodeUrl = Url::currentBackendPage(['safemode' => '0'] + $csrfToken->getUrlParams());
    }

    $content .= '
        <p><a class="btn btn-safemode-activate" href="' . $safemodeUrl . '" data-pjax="false">' . (Core::isSafeMode() ? I18n::msg('safemode_deactivate') : I18n::msg('safemode_activate')) . '</a></p>


        <h3>' . I18n::msg('setup') . '</h3>
        <p>' . I18n::msg('setup_text') . '</p>
        <p><a class="btn btn-setup" href="' . Url::currentBackendPage(['func' => 'setup'] + $csrfToken->getUrlParams()) . '" data-confirm="' . I18n::msg('setup_restart') . '?" data-pjax="false">' . I18n::msg('setup') . '</a></p>';
}
$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('system_features'));
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
            <td>' . PHP_VERSION . ' <a class="rex-link-expanded" href="' . Url::backendPage('system/phpinfo') . '" title="phpinfo" onclick="newWindow(\'phpinfo\', this.href, 1000,800,\',status=yes,resizable=yes\');return false;"><i class="rex-icon rex-icon-phpinfo"></i></a></td>
        </tr>
        <tr>
            <th>' . I18n::msg('path') . '</th>
			<td>
			<div class="rex-word-break">' . Path::base() . '</div>
			</td>
        </tr>
    </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('installation'));
$fragment->setVar('content', $content, false);
$sideContent[] = $fragment->parse('core/page/section.php');

$sql = Sql::factory();

$content = '
    <table class="table">
        <tr>
            <th class="rex-table-width-3">' . I18n::msg('version') . '</th>
            <td>' . $sql->getDbType() . ' ' . $sql->getDbVersion() . '</td>
        </tr>
        <tr>
            <th>' . I18n::msg('name') . '</th>
            <td><span class="rex-word-break">' . $dbconfig->name . '</span></td>
        </tr>
        <tr>
            <th>' . I18n::msg('host') . '</th>
            <td>' . $dbconfig->host . '</td>
        </tr>
    </table>';

$fragment = new rex_fragment();
$fragment->setVar('title', I18n::msg('database'));
$fragment->setVar('content', $content, false);
$sideContent[] = $fragment->parse('core/page/section.php');

$content = '';

$formElements = [];

$n = [];
$n['label'] = '<label for="rex-id-server" class="required">' . I18n::msg('server') . '</label>';
$n['field'] = '<input class="form-control" type="url" id="rex-id-server" name="settings[server]" value="' . rex_escape(Core::getServer()) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-servername" class="required">' . I18n::msg('servername') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-servername" name="settings[servername]" value="' . rex_escape(Core::getServerName()) . '" required />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-lang" class="required">' . I18n::msg('backend_language') . '</label>';
$n['field'] = $selLang->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-error-email" class="required">' . I18n::msg('error_email') . '</label>';
$n['field'] = '<input class="form-control" type="email" id="rex-id-error-email" name="settings[error_email]" value="' . rex_escape(Core::getErrorEmail()) . '" required />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

foreach (rex_system_setting::getAll() as $setting) {
    $field = $setting->getField();
    if (!($field instanceof BaseField)) {
        throw new rex_exception($setting::class . '::getField() must return a BaseField!');
    }
    $field->setAttribute('name', 'settings[' . $setting->getKey() . ']');
    $content .= $field->get();
}

$formElements = [];

$editor = Editor::factory();
$configYml = Path::coreData('config.yml');
if ($url = $editor->getUrl($configYml, 0)) {
    $n = [];
    $n['label'] = '';
    $n['field'] = $n['field'] = '<a class="btn btn-sm btn-primary" href="' . $url . '">' . I18n::msg('system_editor_open_file', Path::basename($configYml)) . '</a>';
    $n['note'] = I18n::msg('system_edit_config_note');
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="sendit"' . Core::getAccesskey(I18n::msg('system_update'), 'save') . '>' . I18n::msg('system_update') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', I18n::msg('system_settings'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$mainContent[] = '
<form id="rex-form-system-setup" action="' . Url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="updateinfos" />
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>';

$content = '<p>' . I18n::msg('system_editor_note') . '</p>';

$viaCookie = array_key_exists('editor', $_COOKIE);
if ($viaCookie) {
    $content .= rex_view::info(I18n::msg('system_editor_note_cookie'));
}

$formElements = [];

$selEditor = new rex_select();
$selEditor->setStyle('class="form-control"');
$selEditor->setName('editor[name]');
$selEditor->setId('rex-id-editor');
$selEditor->setAttribute('class', 'form-control selectpicker');
$selEditor->setSize(1);
$selEditor->setSelected($editor->getName());
$selEditor->addArrayOptions(['' => I18n::msg('system_editor_no_editor')] + $editor->getSupportedEditors());

$n = [];
$n['label'] = '<label for="rex-id-editor">' . I18n::msg('system_editor_name') . '</label>';
$n['field'] = $selEditor->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="rex-id-editor-basepath">' . I18n::msg('system_editor_basepath') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-editor-basepath" name="editor[basepath]" value="' . rex_escape($editor->getBasepath()) . '" />';
$n['note'] = I18n::msg('system_editor_basepath_note');
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];
$class = 'rex-form-aligned';

if (!$viaCookie) {
    $n = [];
    $n['field'] = '<button class="btn btn-save ' . $class . '" type="submit" name="editor[update_cookie]" value="0">' . I18n::msg('system_editor_update_configyml') . '</button>';
    $formElements[] = $n;
    $class = '';
}

$n = [];
$n['field'] = '<button class="btn btn-save ' . $class . '" type="submit" name="editor[update_cookie]" value="1">' . I18n::msg('system_editor_update_cookie') . '</button>';
$formElements[] = $n;

if ($viaCookie) {
    $n = [];
    $n['field'] = '<button class="btn btn-delete" type="submit" name="editor[delete_cookie]" value="1">' . I18n::msg('system_editor_delete_cookie') . '</button>';
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', I18n::msg('system_editor'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$mainContent[] = '
<form id="rex-form-system-setup" action="' . Url::currentBackendPage() . '" method="post">
    <input type="hidden" name="func" value="update_editor" />
    ' . $csrfToken->getHiddenField() . '
    ' . $content . '
</form>';

$fragment = new rex_fragment();
$fragment->setVar('content', [implode('', $mainContent), implode('', $sideContent)], false);
$fragment->setVar('classes', ['col-lg-8', 'col-lg-4'], false);
echo $fragment->parse('core/page/grid.php');
