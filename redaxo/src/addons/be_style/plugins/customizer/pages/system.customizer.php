<?php

$curDir = rex_path::plugin('be_style', 'customizer');

$error = [];
$config = [];
$info = '';
$success = '';

// save config

if (rex_post('btn_save', 'string') != '') {
	// set config
	
	$tempConfig = array();
	$newConfig = array();
	
	$newConfig = rex_post('settings','array');

	$tempConfig['codemirror'] = 0;
	if ($newConfig['codemirror'] == 1) {
		$tempConfig['codemirror'] = 1;
	}
	
	$tempConfig['codemirror_theme'] = htmlspecialchars($newConfig['codemirror_theme']);
	
	$tempConfig['projectname'] = htmlspecialchars($newConfig['projectname']);
	
	$labelcolor = $newConfig['labelcolor'];
	if ($labelcolor == '') {
       $tempConfig['labelcolor'] = '';
	} elseif (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $labelcolor)) {
        $tempConfig['labelcolor'] = htmlspecialchars($labelcolor);
	} else {
		$error[] = rex_i18n::msg('customizer_labelcolor_error');
    }
	
	$tempConfig['showlink'] = 0;
	if ($newConfig['showlink'] == 1) {
		$tempConfig['showlink'] = 1;
	}
	
	$tempConfig['textarea'] = 0;
	if ($newConfig['textarea'] == 1) {
		$tempConfig['textarea'] = 1;
	}
	
	$tempConfig['liquid'] = 0;
	if ($newConfig['liquid'] == 1) {
		$tempConfig['liquid'] = 1;
	}
	
	$tempConfig['nav_flyout'] = 0;
	if ($newConfig['nav_flyout'] == 1) {
		$tempConfig['nav_flyout'] = 1;
	}

	// save config	
	
	if(empty($error) && rex_plugin::get('be_style', 'customizer')->setConfig($tempConfig)) {
		$success = rex_i18n::msg('customizer_config_updated');
	} else {
		$error[] = rex_i18n::msg('customizer_config_update_failed');
	}
}

// load config

$config = rex_plugin::get('be_style', 'customizer')->getConfig();

// build elements

$themes = [];
foreach (glob($curDir . '/assets/vendor/codemirror/theme/*.css') as $filename) {
    $themes[] = substr(basename($filename), 0, -4);
}

$tselect = new rex_select();
$tselect->setId('customizer-codemirror_theme');
$tselect->setName('settings[codemirror_theme]');
$tselect->setSize(1);
$tselect->setAttribute('class', 'form-control');
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

$formElements = [];

$n = [];
$n['label'] = '<label for="customizer-codemirror">' . rex_i18n::msg('customizer_codemirror_check') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-codemirror" name="settings[codemirror]" value="1" ' . ($config['codemirror'] ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-codemirror_theme">' . rex_i18n::msg('customizer_codemirror_theme') . '</label>';
$n['field'] = $tselect->get();
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
$n['field'] = '<input class="form-control" id="customizer-labelcolor" type="text" name="settings[labelcolor]" disabled="disabled" value="' . $config['labelcolor'] . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-showlink">' . rex_i18n::msg('customizer_showlink') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-showlink" name="settings[showlink]" disabled="disabled" value="" ' . ($config['showlink'] ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-textarea">' . rex_i18n::msg('customizer_textarea') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-textarea" name="settings[textarea]" disabled="disabled" value="" ' . ($config['textarea'] ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-liquid">' . rex_i18n::msg('customizer_liquid') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-liquid" name="settings[liquid]" disabled="disabled" value="" ' . ($config['liquid'] ? 'checked="checked" ' : '') . '/>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-nav_flyout">' . rex_i18n::msg('customizer_nav_flyout') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-nav_flyout" name="settings[nav_flyout]" disabled="disabled" value="" ' . ($config['nav_flyout'] ? 'checked="checked" ' : '') . '/>';
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