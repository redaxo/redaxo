<?php

/**
 * Addon Framework Classes.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('phpmailer');

$message = '';

if ('' != rex_post('btn_save', 'string') || '' != rex_post('btn_check', 'string')) {
    $addon->setConfig(rex_post('settings', [
        ['fromname', 'string'],
        ['from', 'string'],
        ['confirmto', 'string'],
        ['bcc', 'string'],
        ['mailer', 'string'],
        ['host', 'string'],
        ['port', 'int'],
        ['charset', 'string'],
        ['wordwrap', 'int'],
        ['encoding', 'string'],
        ['username', 'string'],
        ['password', 'string'],
        ['smtpsecure', 'string'],
        ['security_mode', 'boolean'],
        ['smtpauth', 'boolean'],
        ['priority', 'int'],
        ['smtp_debug', 'int'],
        ['test_address', 'string'],
        ['logging', 'int'],
        ['archive', 'boolean'],
    ]));

    if ('' != rex_post('btn_check', 'string')) {
        $settings = rex_post('settings', 'array', []);

        if (false == rex_validator::factory()->email($settings['from']) || false == rex_validator::factory()->email($settings['test_address'])) {
            $warning = $addon->i18n('check_settings_not_tested');
            echo rex_view::warning($warning);
        } else {
            rex_response::sendRedirect(rex_url::backendPage('phpmailer/checkmail'));
        }
    }

    $message = $addon->i18n('config_saved_successful');
}

$emptymail = '1';
if ('' == $addon->getConfig('from') || '' == $addon->getConfig('test_address')) {
    $emptymail = '';
}
$sel_mailer = new rex_select();
$sel_mailer->setId('phpmailer-mailer');
$sel_mailer->setName('settings[mailer]');
$sel_mailer->setSize(1);
$sel_mailer->setAttribute('class', 'form-control selectpicker');
$sel_mailer->setSelected($addon->getConfig('mailer'));
foreach (['mail', 'sendmail', 'smtp'] as $type) {
    $sel_mailer->addOption($type, $type);
}

$sel_security_mode = new rex_select();
$sel_security_mode->setId('security_mode');
$sel_security_mode->setName('settings[security_mode]');
$sel_security_mode->setSize(1);
$sel_security_mode->setAttribute('class', 'form-control selectpicker');
$sel_security_mode->setSelected($addon->getConfig('security_mode'));
foreach ([0 => $addon->i18n('security_mode_manual'), 1 => $addon->i18n('security_mode_auto')] as $i => $type) {
    $sel_security_mode->addOption($type, $i);
}

$sel_smtpauth = new rex_select();
$sel_smtpauth->setId('phpmailer-smtpauth');
$sel_smtpauth->setName('settings[smtpauth]');
$sel_smtpauth->setSize(1);
$sel_smtpauth->setAttribute('class', 'form-control selectpicker');
$sel_smtpauth->setSelected($addon->getConfig('smtpauth'));
foreach ([0 => $addon->i18n('smtp_auth_off'), 1 => $addon->i18n('smtp_auth_on')] as $i => $type) {
    $sel_smtpauth->addOption($type, $i);
}

$sel_smtpsecure = new rex_select();
$sel_smtpsecure->setId('phpmailer-smtpsecure');
$sel_smtpsecure->setName('settings[smtpsecure]');
$sel_smtpsecure->setSize(1);
$sel_smtpsecure->setAttribute('class', 'form-control selectpicker');
$sel_smtpsecure->setSelected($addon->getConfig('smtpsecure'));
foreach (['' => $addon->i18n('no'), 'ssl' => 'ssl', 'tls' => 'tls'] as $type => $name) {
    $sel_smtpsecure->addOption($name, $type);
}

$sel_encoding = new rex_select();
$sel_encoding->setId('phpmailer-encoding');
$sel_encoding->setName('settings[encoding]');
$sel_encoding->setSize(1);
$sel_encoding->setAttribute('class', 'form-control selectpicker');
$sel_encoding->setSelected($addon->getConfig('encoding'));
foreach (['7bit', '8bit', 'binary', 'base64', 'quoted-printable'] as $enc) {
    $sel_encoding->addOption($enc, $enc);
}

$sel_priority = new rex_select();
$sel_priority->setid('phpmailer-priority');
$sel_priority->setName('settings[priority]');
$sel_priority->setSize(1);
$sel_priority->setAttribute('class', 'form-control selectpicker');
$sel_priority->setSelected($addon->getConfig('priority'));
foreach ([0 => $addon->i18n('disabled'), 1 => $addon->i18n('high'), 3 => $addon->i18n('normal'), 5 => $addon->i18n('low')] as $no => $name) {
    $sel_priority->addOption($name, $no);
}

$sel_log = new rex_select();
$sel_log->setid('phpmailer-log');
$sel_log->setName('settings[logging]');
$sel_log->setSize(1);
$sel_log->setAttribute('class', 'form-control selectpicker');
$sel_log->setSelected($addon->getConfig('logging'));
$sel_log->addOption($addon->i18n('log_no'), 0);
$sel_log->addOption($addon->i18n('log_errors'), rex_mailer::LOG_ERRORS);
$sel_log->addOption($addon->i18n('log_all'), rex_mailer::LOG_ALL);

$sel_archive = new rex_select();
$sel_archive->setid('phpmailer-archive');
$sel_archive->setName('settings[archive]');
$sel_archive->setSize(1);
$sel_archive->setAttribute('class', 'form-control selectpicker');
$sel_archive->setSelected((int) $addon->getConfig('archive'));
$sel_archive->addOption($addon->i18n('log_no'), 0);
$sel_archive->addOption($addon->i18n('log_yes'), 1);

$sel_debug = new rex_select();
$sel_debug->setid('phpmailer-smtp_debug');
$sel_debug->setName('settings[smtp_debug]');
$sel_debug->setSize(1);
$sel_debug->setAttribute('class', 'form-control selectpicker');
$sel_debug->setSelected($addon->getConfig('smtp_debug'));
foreach ([0 => $addon->i18n('smtp_debug_0'), 1 => $addon->i18n('smtp_debug_1'), 2 => $addon->i18n('smtp_debug_2'), 3 => $addon->i18n('smtp_debug_3'), 4 => $addon->i18n('smtp_debug_4')] as $no => $name) {
    $sel_debug->addOption($name, $no);
}

if ('' != $message) {
    echo rex_view::success($message);
}

$content = '';
$content .= '<div class="row">';
$content .= '<div class="col-sm-6">';
$content .= '<fieldset><legend>' . $addon->i18n('email_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-fromname">' . $addon->i18n('sender_name') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-fromname" type="text" name="settings[fromname]" value="' . rex_escape($addon->getConfig('fromname')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-from">' . $addon->i18n('sender_email') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-from" type="email" name="settings[from]" placeholder="name@example.tld" value="' . rex_escape($addon->getConfig('from')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-testaddress">' . $addon->i18n('checkmail_test_address') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-testaddress" type="email" name="settings[test_address]" placeholder="test@example.tld" value="' . rex_escape($addon->getConfig('test_address')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-confirmto">' . $addon->i18n('confirm') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-confirmto" type="email" name="settings[confirmto]" placeholder="confirm@example.tld" value="' . rex_escape($addon->getConfig('confirmto')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-bcc">' . $addon->i18n('bcc') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-bcc" type="email" name="settings[bcc]" placeholder="bcc@example.tld" value="' . rex_escape($addon->getConfig('bcc')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-mailer">' . $addon->i18n('mailertype') . '</label>';
$n['field'] = $sel_mailer->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';
$content .= '<fieldset id="smtpsettings"><legend>' . $addon->i18n('smtp_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-host">' . $addon->i18n('host') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-host" placeholder="smtp.example.tld" type="text" name="settings[host]" value="' . rex_escape($addon->getConfig('host')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-port">' . $addon->i18n('port') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-port" type="number" name="settings[port]" value="' . rex_escape($addon->getConfig('port')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label data-toggle="tooltip" title="' . $addon->i18n('security_mode_help') . '" for="security_mode">' . rex_escape($addon->i18n('security_mode')) . ' <i class="rex-icon fa-question-circle"></i></label>';
$n['field'] = $sel_security_mode->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$content .= '<div id="securetype">';
$n = [];
$n['label'] = '<label for="phpmailer-smtpsecure">' . $addon->i18n('smtp_secure') . '</label>';
$n['field'] = $sel_smtpsecure->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '</div>';

$n = [];
$n['label'] = '<label for="phpmailer-smtpauth">' . $addon->i18n('smtp_auth') . '</label>';
$n['field'] = $sel_smtpauth->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '<div id="smtpauthlogin">';

$n = [];
$n['label'] = '<label for="phpmailer-username">' . $addon->i18n('smtp_username') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-username" type="text" name="settings[username]" value="' . rex_escape($addon->getConfig('username')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-password">' . $addon->i18n('smtp_password') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-password" type="password" name="settings[password]" value="' . rex_escape($addon->getConfig('password')) . '" autocomplete="new-password" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '</div>';

$n = [];
$n['label'] = '<label for="phpmailer-smtp_debug">' . $addon->i18n('smtp_debug') . '</label>';
$n['field'] = $sel_debug->get().'<p class="help-block rex-note"> ' . $addon->i18n('smtp_debug_info').'</p>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></div>';
$content .= '<fieldset class="col-sm-6"><legend>' . $addon->i18n('dispatch_options') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="phpmailer-charset">' . $addon->i18n('charset') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-charset" type="text" name="settings[charset]" value="' . rex_escape($addon->getConfig('charset')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-wordwrap">' . $addon->i18n('wordwrap') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-wordwrap" type="number" name="settings[wordwrap]" value="' . rex_escape($addon->getConfig('wordwrap')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-encoding">' . $addon->i18n('encoding') . '</label>';
$n['field'] = $sel_encoding->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-priority">' . $addon->i18n('priority') . '</label>';
$n['field'] = $sel_priority->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-log">' . $addon->i18n('logging') . '</label>';
$n['field'] = $sel_log->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-log">' . $addon->i18n('archive') . '</label>';
$n['field'] = $sel_archive->get();
$n['note'] = rex_i18n::rawMsg('phpmailer_archive_info', rex_mailer::logFolder(), '...'.substr(rex_mailer::logFolder(), -30));
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></div>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-reset pull-right" type="reset" name="btn_reset" value="' . $addon->i18n('reset') . '" data-confirm="' . $addon->i18n('reset_info') . '">' . $addon->i18n('reset') . '</button>';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-save pull-right" type="submit" name="btn_check" value="' . $addon->i18n('check_settings_btn') . '">' . $addon->i18n('check_settings_btn') . '</button>';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-save pull-right" type="submit" name="btn_save" value="' . $addon->i18n('save') . '">' . $addon->i18n('save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('config_settings'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');
echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';
?>
<script>
    $('[data-toggle="tooltip"]').tooltip();
    $('#smtpsettings').toggle(
        $('#phpmailer-mailer').find("option[value='smtp']").is(":checked")
    );
     $('#securetype').toggle(
        $('#security_mode').find("option[value='0']").is(":checked")
    );

     $('#smtpauthlogin').toggle(
        $('#phpmailer-smtpauth').find("option[value='1']").is(":checked")
    );

    $('#phpmailer-mailer').change(function(){
        if ($(this).val() == 'smtp') {
            $('#smtpsettings').slideDown();
        } else {
            $('#smtpsettings').slideUp();
        }
    });

        $('#security_mode').change(function(){
        if ($(this).val() == '0') {
            $('#securetype').slideDown();
        } else {
            $('#securetype').slideUp();
        }
    });

        $('#phpmailer-smtpauth').change(function(){
        if ($(this).val() == '1') {
            $('#smtpauthlogin').slideDown();
        } else {
            $('#smtpauthlogin').slideUp();
        }
    });

</script>
