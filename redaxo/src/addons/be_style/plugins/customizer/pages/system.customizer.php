<?php

$error = [];
$config = [];
$info = '';
$success = '';

if (rex_post('btn_save', 'string') != '') {
    // set config

    $tempConfig = [];
    $newConfig = [];

    $newConfig = rex_post('settings', 'array');
    $tempConfig = rex_plugin::get('be_style', 'customizer')->getConfig();

    $tempConfig['codemirror'] = 0;
    if (isset($newConfig['codemirror']) && $newConfig['codemirror'] == 1) {
        $tempConfig['codemirror'] = 1;
    }

    $tempConfig['codemirror-selectors'] = '';
    if (isset($newConfig['codemirror-selectors'])) {
        $tempConfig['codemirror-selectors'] = $newConfig['codemirror-selectors'];
    }

    $tempConfig['codemirror-langs'] = 0;
    if (isset($newConfig['codemirror-langs']) && $newConfig['codemirror-langs'] == 1) {
        $tempConfig['codemirror-langs'] = 1;
    }

    $tempConfig['codemirror-tools'] = 0;
    if (isset($newConfig['codemirror-tools']) && $newConfig['codemirror-tools'] == 1) {
        $tempConfig['codemirror-tools'] = 1;
    }

    $tempConfig['codemirror_theme'] = htmlspecialchars($newConfig['codemirror_theme']);

    $labelcolor = $newConfig['labelcolor'];
    if ($labelcolor == '') {
        $tempConfig['labelcolor'] = '';
    } else {
        $tempConfig['labelcolor'] = htmlspecialchars($labelcolor);
    }

    $tempConfig['showlink'] = 0;
    if (isset($newConfig['showlink']) && $newConfig['showlink'] == 1) {
        $tempConfig['showlink'] = 1;
    }

    // save config

    if (empty($error) && rex_plugin::get('be_style', 'customizer')->setConfig($tempConfig)) {
        $success = rex_i18n::msg('customizer_config_updated');
    } else {
        $error[] = rex_i18n::msg('customizer_config_update_failed');
    }

    $_SESSION['codemirror_reload'] = time();
}

// load config

$config = rex_plugin::get('be_style', 'customizer')->getConfig();
if (!isset($config['codemirror-langs'])) {
    $config['codemirror-langs'] = 0;
}
if (!isset($config['codemirror-tools'])) {
    $config['codemirror-tools'] = 0;
}
if (!isset($config['codemirror-selectors'])) {
    $config['codemirror-selectors'] = '';
}

// build elements

$curDir = $this->getAssetsUrl('vendor/');

$themes = [];
foreach (glob($curDir . '/codemirror/theme/*.css') as $filename) {
    $themes[] = substr(basename($filename), 0, -4);
}

$tselect = new rex_select();
$tselect->setId('customizer-codemirror_theme');
$tselect->setName('settings[codemirror_theme]');
$tselect->setSize(1);
$tselect->setAttribute('class', 'form-control selectpicker');
$tselect->setAttribute('data-live-search', 'true');
$tselect->setSelected($config['codemirror_theme']);
foreach ($themes as $theme) {
    $tselect->addOption($theme, $theme);
}

// messages

if (!empty($error)) {
    echo rex_view::error(implode('<br />', $error));
}

if ($info != '') {
    echo rex_view::info($info);
}

if ($success != '') {
    echo rex_view::success($success);
}

// output

$content = '';

// form - Funktionen

$content .= '<fieldset><legend>' . rex_i18n::msg('customizer_features') . '</legend>';
$content .= '<input type="hidden" name="settings[codemirror]" value="0"/>';
$content .= '<input type="hidden" name="settings[codemirror-langs]" value="0"/>';
$content .= '<input type="hidden" name="settings[codemirror-tools]" value="0"/>';

$formElements = [];

$n = [];
$n['label'] = '<label for="customizer-codemirror">' . rex_i18n::msg('customizer_codemirror_check') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror" name="settings[codemirror]" value="1" ' . ($config['codemirror'] ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-selectors">' . rex_i18n::msg('customizer_codemirror_selectors') . '</label>';
$n['field'] = '<textarea rows="2" class="form-control" id="customizer-codemirror-selectors" name="settings[codemirror-selectors]">' . htmlspecialchars($config['codemirror-selectors']) . '</textarea>';
$n['note'] = rex_i18n::msg('customizer_codemirror_selectors_info');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror_theme">' . rex_i18n::msg('customizer_codemirror_theme') . '</label>';
$n['field'] = $tselect->get();
$formElements[] = $n;

$n = [];
$n['label'] = '';
$n['field'] = '<p>' . rex_i18n::msg('customizer_codemirror_info') . '</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-langs">' . rex_i18n::msg('customizer_codemirror_langs') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror-langs" name="settings[codemirror-langs]" value="1" ' . ($config['codemirror-langs'] ? 'checked="checked" ' : '') . '/>';
$n['field'] .= ' '.rex_i18n::msg('customizer_codemirror_langs_text');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror-tools">' . rex_i18n::msg('customizer_codemirror_tools') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror-tools" name="settings[codemirror-tools]" value="1" ' . ($config['codemirror-tools'] ? 'checked="checked" ' : '') . '/>';
$n['field'] .= ' '.rex_i18n::msg('customizer_codemirror_tools_text');
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
$n['field'] = '<input class="form-control" id="customizer-labelcolor" type="text" name="settings[labelcolor]" value="' . htmlspecialchars($config['labelcolor']) . '" />';
$n['note'] = rex_i18n::msg('customizer_labelcolor_notice');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-showlink">' . rex_i18n::msg('customizer_showlink') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-showlink" name="settings[showlink]" value="1" ' . ($config['showlink'] ? 'checked="checked" ' : '') . ' />';
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
