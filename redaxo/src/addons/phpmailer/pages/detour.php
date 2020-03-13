<?php
/**
 * Adds the option to send all outgoing mails to one specific email address.
 * This allows easy testing/debugging of all forms.
 *
 * @author markus[dot]dick[at]novinet[dot]de Markus Dick
 *
 * @package redaxo5
 */

$addon = rex_addon::get('phpmailer');

if ('' != rex_post('btn_save', 'string')) {
    $settings = rex_post('settings', 'array', []);

    // if valid email save and show success
    if (true == rex_validator::factory()->email($settings['detour-email'])) {
        $addon->setConfig(rex_post('settings', [
            ['detour-mode', 'string'],
            ['detour-email', 'string']
        ]));
        echo rex_view::success($addon->i18n('config_saved_successful'));

    // else dont save and show error
    } else {
        echo rex_view::warning($addon->i18n('detour_email_validation_error'));
    }
}

// content
$content = '';
$content .= '<div class="row">';
$content .= '<div class="col-sm-6">';
$content .= '<fieldset>';


// select field: detour mode enabled/disabled
$sel_mode = new rex_select();
$sel_mode->setId('phpmailer-detour-mode');
$sel_mode->setName('settings[detour-mode]');
$sel_mode->setSize(1);
$sel_mode->setAttribute('class', 'form-control selectpicker');

// standard is disabled
$selected = $addon->getConfig('detour-mode') ?: 'disabled';
$sel_mode->setSelected($selected);
foreach (['enabled', 'disabled'] as $type) {
    $sel_mode->addOption($addon->i18n('detour_'.$type), $type);
}

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-detour-mode">' . $addon->i18n('detour_email_redirect') . '</label>';
$n['field'] = $sel_mode->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

// sub content
$content .= '<fieldset id="detoursettings"><legend>' . $addon->i18n('detour_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-detour-email">' . $addon->i18n('detour_email') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-detour-email" type="email" name="settings[detour-email]" placeholder="name@example.tld" value="' . rex_escape($addon->getConfig('detour-email')) . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></div></div>';

// submit buttons
$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save pull-right" type="submit" name="btn_save" value="' . $addon->i18n('save') . '">' . $addon->i18n('save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

// the form
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('detour_config'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');
echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';
?>

<script>
 $('#detoursettings').toggle(
    $('#phpmailer-detour-mode').find('option[value="enabled"]').is(':checked')
);

$('#phpmailer-detour-mode').change(function(){
    if ($(this).val() == 'enabled') {
        $('#detoursettings').slideDown();
    } else {
        $('#detoursettings').slideUp();
    }
});
</script>
