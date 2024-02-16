<?php

$addon = rex_addon::require('be_style');

$success = '';

if ('' != rex_post('btn_save', 'string')) {
    // set config
    $settings = rex_post('settings', [
        ['labelcolor', 'string'],
        ['showlink', 'boolean'],
    ]);

    $addon->setConfig($settings);
    $success = rex_i18n::msg('customizer_config_updated');
}

if ('' != $success) {
    echo rex_view::success($success);
}

// output

$content = '<fieldset><legend>' . rex_i18n::msg('customizer_labeling') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="customizer-labelcolor">' . rex_i18n::msg('customizer_labelcolor') . '</label>';
$n['field'] = '
    <div class="input-group">
    <div class="input-group-addon">
        <input id="customizer-labelcolor-picker" type="color" value="' . rex_escape($addon->getConfig('labelcolor', '')) . '"
            oninput="jQuery(\'#customizer-labelcolor\').val(this.value)" />
    </div>
    <input class="form-control" id="customizer-labelcolor" type="text" name="settings[labelcolor]" value="' . rex_escape($addon->getConfig('labelcolor', '')) . '" oninput="jQuery(\'#customizer-labelcolor-picker\').val(this.value)" />
</div>
';
$n['note'] = rex_i18n::msg('customizer_labelcolor_notice');
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="customizer-showlink">' . rex_i18n::msg('customizer_showlink') . '</label>';
$n['field'] = '<input type="checkbox" id="customizer-showlink" name="settings[showlink]" value="1" ' . ($addon->getConfig('showlink', '') ? 'checked="checked" ' : '') . ' />';
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
