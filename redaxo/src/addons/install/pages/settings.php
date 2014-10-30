<?php

/** @var rex_addon $this */

$content = '';

$settings = rex_post('settings', [
    ['backups', 'bool', false],
    ['api_login', 'string'],
    ['api_key', 'string']
], null);

if (is_array($settings)) {
    foreach ($settings as $key => $value) {
        $this->setConfig($key, $value);
    }
    echo rex_view::success($this->i18n('settings_saved'));
    rex_install_webservice::deleteCache();
}

$content .= '
    <div class="rex-form">
        <form action="' . rex_url::currentBackendPage() . '" method="post">
            <fieldset>
                <h2>' . $this->i18n('settings_general') . '</h2>';


            $formElements = [];

                $n = [];
                $n['reverse'] = true;
                $n['label'] = '<label for="install-settings-backups">' . $this->i18n('settings_backups') . '</label>';
                $n['field'] = '<input id="install-settings-backups" type="checkbox" class="rex-form-checkbox" name="settings[backups]" value="1" ' . ($this->getConfig('backups') ? 'checked="checked" ' : '') . '/>';
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $content .= $fragment->parse('core/form/form.php');


$content .= '
            </fieldset>
            <fieldset>
                <h2>' . $this->i18n('settings_myredaxo_account') . '</h2>';


            $formElements = [];

                $n = [];
                $n['label'] = '<label for="install-settings-api-login">' . $this->i18n('settings_api_login') . '</label>';
                $n['field'] = '<input id="install-settings-api-login" class="rex-form-text" type="text" name="settings[api_login]" value="' . $this->getConfig('api_login') . '" />';
                $formElements[] = $n;

                $n = [];
                $n['label'] = '<label for="install-settings-api-key">' . $this->i18n('settings_api_key') . '</label>';
                $n['field'] = '<input id="install-settings-api-key" class="rex-form-text" type="text" name="settings[api_key]" value="' . $this->getConfig('api_key') . '" />';
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $content .= $fragment->parse('core/form/form.php');


$content .= '
                </fieldset>';


            $formElements = [];

                $n = [];
                $n['field'] = '<button class="rex-button rex-button-primary" type="submit" name="settings[save]" value="1">' . rex_i18n::msg('form_save') . '</button>';
                $formElements[] = $n;

                $fragment = new rex_fragment();
                $fragment->setVar('elements', $formElements, false);
                $content .= $fragment->parse('core/form/submit.php');

$content .= '
        </form>
    </div>';

echo rex_view::content('block', $content, '', $params = ['flush' => true]);
