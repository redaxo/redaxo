<?php

$success = '';
$error = '';

if ('' != rex_post('btn_save', 'string')) {
    // set config
    $settings = rex_post('settings', [
        ['be_style_codemirror', 'boolean'],
        ['be_style_codemirror_selectors', 'string'],
        ['be_style_codemirror_langs', 'boolean'],
        ['be_style_codemirror_tools', 'boolean'],
        ['be_style_codemirror_autoresize', 'boolean'],
        ['be_style_codemirror_options', 'string'],
        ['be_style_codemirror_theme', 'string'],
        ['be_style_codemirror_darktheme', 'string'],
        ['be_style_labelcolor', 'string'],
        ['be_style_showlink', 'boolean'],
    ]);

    // $newConfig = rex_post('settings', 'array');
    // $tempConfig = rex_config::get('be_style')->getConfig();

    // $tempConfig['be_style_codemirror'] = 0;
    // if (isset($newConfig['be_style_codemirror']) && 1 == $newConfig['be_style_codemirror']) {
    //     $tempConfig['be_style_codemirror'] = 1;
    // }

    // $tempConfig['be_style_codemirror_selectors'] = '';
    // if (isset($newConfig['be_style_codemirror_selectors'])) {
    //     $tempConfig['be_style_codemirror_selectors'] = (string) $newConfig['be_style_codemirror_selectors'];
    // }

    // $tempConfig['be_style_codemirror_langs'] = 0;
    // if (isset($newConfig['be_style_codemirror_langs']) && 1 == $newConfig['be_style_codemirror_langs']) {
    //     $tempConfig['be_style_codemirror_langs'] = 1;
    // }
    //
    // $tempConfig['be_style_codemirror_tools'] = 0;
    // if (isset($newConfig['be_style_codemirror_tools']) && 1 == $newConfig['be_style_codemirror_tools']) {
    //     $tempConfig['be_style_codemirror_tools'] = 1;
    // }
    //
    // $tempConfig['be_style_codemirror_autoresize'] = 0;
    // if (isset($newConfig['be_style_codemirror_autoresize']) && 1 == $newConfig['be_style_codemirror_autoresize']) {
    //     $tempConfig['be_style_codemirror_autoresize'] = 1;
    // }

    // $tempConfig['be_style_codemirror_options'] = '';
    // if (isset($newConfig['be_style_codemirror_options'])) {
    //     $tempConfig['be_style_codemirror_options'] = (string) $newConfig['be_style_codemirror_options'];
    // }
    //
    // $tempConfig['be_style_codemirror_theme'] = htmlspecialchars((string) $newConfig['be_style_codemirror_theme']);
    //
    // $tempConfig['be_style_codemirror_darktheme'] = htmlspecialchars((string) $newConfig['be_style_codemirror_darktheme']);
    //
    // $labelcolor = (string) $newConfig['be_style_labelcolor'];
    // if ('' == $labelcolor) {
    //     $tempConfig['be_style_labelcolor'] = '';
    // } else {
    //     $tempConfig['be_style_labelcolor'] = htmlspecialchars($labelcolor);
    // }
    //
    // $tempConfig['be_style_showlink'] = 0;
    // if (isset($newConfig['be_style_showlink']) && 1 == $newConfig['be_style_showlink']) {
    //     $tempConfig['be_style_showlink'] = 1;
    // }

    rex_config::set('core', $settings);
    $success = rex_i18n::msg('customizer_config_updated');
    $_SESSION['be_style_codemirror_reload'] = time();
}

// build elements

$themes = [];
foreach (glob(rex_url::coreAssets('vendor/codemirror/theme/*.css')) as $filename) {
    $themes[] = substr(rex_path::basename($filename), 0, -4);
}

$tselect = new rex_select();
$tselect->setId('customizer-codemirror_theme');
$tselect->setName('settings[be_style_codemirror_theme]');
$tselect->setSize(1);
$tselect->setAttribute('class', 'form-control selectpicker');
$tselect->setAttribute('data-live-search', 'true');
$tselect->setSelected(rex::getConfig('be_style_codemirror_theme'));

$tselectdark = new rex_select();
$tselectdark->setId('customizer-codemirror_darktheme');
$tselectdark->setName('settings[be_style_codemirror_darktheme]');
$tselectdark->setSize(1);
$tselectdark->setAttribute('class', 'form-control selectpicker');
$tselectdark->setAttribute('data-live-search', 'true');
$tselectdark->setSelected(rex::getConfig('be_style_codemirror_darktheme'));

foreach ($themes as $theme) {
    $tselect->addOption($theme, $theme);
    $tselectdark->addOption($theme, $theme);
}

// messages

if ($error) {
    echo rex_view::error($error);
}

if ('' != $success) {
    echo rex_view::success($success);
}

// output
$content = '<p>Customizer bindet den Editor CodeMirror Version 5.65.5 (<a target="_blank" rel="noreferrer noopener" href="https://codemirror.net/">https://codemirror.net/</a>) ein.</p>';

// form - Funktionen

$content .= '<fieldset><legend>' . rex_i18n::msg('customizer_features') . '</legend>';
$content .= '<input type="hidden" name="settings[be_style_codemirror]" value="0"/>';
$content .= '<input type="hidden" name="settings[be_style_codemirror_langs]" value="0"/>';
$content .= '<input type="hidden" name="settings[be_style_codemirror_tools]" value="0"/>';

$formElements = [];

$n = [];
$n['label'] = '<label for="customizer-codemirror">' . rex_i18n::msg('customizer_codemirror_check') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror" name="settings[be_style_codemirror]" value="1" ' . (rex::getConfig('be_style_codemirror') ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-selectors">' . rex_i18n::msg('customizer_codemirror_selectors') . '</label>';
$n['field'] = '<textarea rows="2" class="form-control" id="customizer-codemirror-selectors" name="settings[be_style_codemirror_selectors]">' . rex_escape(rex::getConfig('be_style_codemirror_selectors', '')) . '</textarea>';
$n['note'] = rex_i18n::msg('customizer_codemirror_selectors_info');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror_theme">' . rex_i18n::msg('customizer_codemirror_theme') . '</label>';
$n['field'] = $tselect->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror_darktheme">' . rex_i18n::msg('customizer_codemirror_darktheme') . '</label>';
$n['field'] = $tselectdark->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-langs">' . rex_i18n::msg('customizer_codemirror_langs') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror-langs" name="settings[be_style_codemirror_langs]" value="1" ' . (rex::getConfig('be_style_codemirror_langs') ? 'checked="checked" ' : '') . '/>';
$n['field'] .= ' ' . rex_i18n::msg('customizer_codemirror_langs_text');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-tools">' . rex_i18n::msg('customizer_codemirror_tools') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror-tools" name="settings[be_style_codemirror_tools]" value="1" ' . (rex::getConfig('be_style_codemirror_tools') ? 'checked="checked" ' : '') . '/>';
$n['field'] .= ' ' . rex_i18n::msg('customizer_codemirror_tools_text');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-autoresize">' . rex_i18n::msg('customizer_codemirror_autoresize') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror-autoresize" name="settings[be_style_codemirror_autoresize]" value="1" ' . (rex::getConfig('be_style_codemirror_autoresize') ? 'checked="checked" ' : '') . '/>';
$n['field'] .= ' ' . rex_i18n::msg('customizer_codemirror_autoresize_text');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-options">' . rex_i18n::msg('customizer_codemirror_options') . '</label>';
$n['field'] = '<textarea rows="4" class="form-control" id="customizer-codemirror-options" name="settings[be_style_codemirror_options]">' . rex_escape(rex::getConfig('be_style_codemirror_options', '')) . '</textarea>';
$n['note'] = rex_i18n::msg('customizer_codemirror_options_info');
$formElements[] = $n;

$n = [];
$n['label'] = '';
$n['field'] = '<p>' . rex_i18n::msg('customizer_codemirror_info') . '</p>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

// form - Ergänzungen

$content .= '<fieldset><legend>' . rex_i18n::msg('customizer_labeling') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="customizer-labelcolor">' . rex_i18n::msg('customizer_labelcolor') . '</label>';
$n['field'] = '
    <div class="input-group">
        <div class="input-group-addon">
            <input id="customizer-labelcolor-picker" type="color" value="' . rex_escape(rex::getConfig('be_style_labelcolor', '')) . '" oninput="jQuery(\'#customizer-labelcolor\').val(this.value)" />
        </div>
        <input class="form-control" id="customizer-labelcolor" type="text" name="settings[be_style_labelcolor]" value="' . rex_escape(rex::getConfig('be_style_labelcolor', '')) . '" oninput="jQuery(\'#customizer-labelcolor-picker\').val(this.value)" />
    </div>
';
$n['note'] = rex_i18n::msg('customizer_labelcolor_notice');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-showlink">' . rex_i18n::msg('customizer_showlink') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-showlink" name="settings[be_style_showlink]" value="1" ' . (rex::getConfig('be_style_showlink') ? 'checked="checked" ' : '') . ' />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

// form - Button

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="' . rex_i18n::msg('customizer_update') . '">' . rex_i18n::msg('customizer_update') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

// section
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('customizer'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>
';
