<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\View\Fragment;
use Redaxo\Core\View\Message;

use function Redaxo\Core\View\escape;

$addon = Addon::get('install');

$panel = '';

$configFile = $addon->getDataPath('config.json');
$config = array_merge([
    'backups' => false,
    'api_login' => null,
    'api_key' => null,
], File::getCache($configFile));

$newConfig = Request::post('settings', [
    ['backups', 'bool', false],
    ['api_login', 'string'],
    ['api_key', 'string'],
], null);

if (is_array($newConfig)) {
    $config = $newConfig;
    if (File::putCache($configFile, $config)) {
        echo Message::success($addon->i18n('settings_saved'));
        rex_install_webservice::deleteCache();
    } else {
        echo Message::error($addon->i18n('settings_error', $configFile));
    }
}

$panel .= '
            <fieldset>
                <legend>' . $addon->i18n('settings_general') . '</legend>';

$formElements = [];

$n = [];
$n['reverse'] = true;
$n['label'] = '<label>' . $addon->i18n('settings_backups') . '</label>';
$n['field'] = '<input type="checkbox"  name="settings[backups]" value="1" ' . ($config['backups'] ? 'checked="checked" ' : '') . '/>';
$n['note'] = $addon->i18n('settings_backups_note');
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$panel .= $fragment->parse('core/form/checkbox.php');

$panel .= '
            </fieldset>
            <fieldset>
                <legend>' . $addon->i18n('settings_myredaxo_account') . '</legend>

                <p>' . $addon->i18n('settings_myredaxo_notice') . '</p>';

$formElements = [];

$n = [];
$n['label'] = '<label for="install-settings-api-login">' . $addon->i18n('settings_api_login') . '</label>';
$n['field'] = '<input class="form-control" id="install-settings-api-login" type="text" name="settings[api_login]" value="' . escape($config['api_login']) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="install-settings-api-key">' . $addon->i18n('settings_api_key') . '</label>';
$n['field'] = '<input class="form-control" id="install-settings-api-key" type="text" name="settings[api_key]" value="' . escape($config['api_key']) . '" />';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$panel .= $fragment->parse('core/form/form.php');

$panel .= '
                </fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="settings[save]" value="1">' . I18n::msg('form_save') . '</button>';
$formElements[] = $n;

$fragment = new Fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new Fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('subpage_settings'), false);
$fragment->setVar('body', $panel, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

$content = '
    <form action="' . Url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';

echo $content;
