<?php

/**
 * Addon Framework Classes.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$message = '';

if (rex_post('btn_save', 'string') != '') {
    $this->setConfig(rex_post('settings', [
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
        ['log', 'int', 1],
    ]));

    $message = $this->i18n('config_saved_successful');
}

$emptymail = '1';
if ($this->getConfig('from') == '' || $this->getConfig('test_address') == '') {
    $emptymail = '';
}
$sel_mailer = new rex_select();
$sel_mailer->setId('phpmailer-mailer');
$sel_mailer->setName('settings[mailer]');
$sel_mailer->setSize(1);
$sel_mailer->setAttribute('class', 'form-control selectpicker');
$sel_mailer->setSelected($this->getConfig('mailer'));
foreach (['mail', 'sendmail', 'smtp'] as $type) {
    $sel_mailer->addOption($type, $type);
}

$sel_security_mode = new rex_select();
$sel_security_mode->setId('security_mode');
$sel_security_mode->setName('settings[security_mode]');
$sel_security_mode->setSize(1);
$sel_security_mode->setAttribute('class', 'form-control selectpicker');
$sel_security_mode->setSelected($this->getConfig('security_mode'));
foreach ([0 => $this->i18n('security_mode_manual'), 1 => $this->i18n('security_mode_auto')] as $i => $type) {
    $sel_security_mode->addOption($type, $i);
}

$sel_smtpauth = new rex_select();
$sel_smtpauth->setId('phpmailer-smtpauth');
$sel_smtpauth->setName('settings[smtpauth]');
$sel_smtpauth->setSize(1);
$sel_smtpauth->setAttribute('class', 'form-control selectpicker');
$sel_smtpauth->setSelected($this->getConfig('smtpauth'));
foreach ([0 => $this->i18n('smtp_auth_off'), 1 => $this->i18n('smtp_auth_on')] as $i => $type) {
    $sel_smtpauth->addOption($type, $i);
}

$sel_smtpsecure = new rex_select();
$sel_smtpsecure->setId('phpmailer-smtpsecure');
$sel_smtpsecure->setName('settings[smtpsecure]');
$sel_smtpsecure->setSize(1);
$sel_smtpsecure->setAttribute('class', 'form-control selectpicker');
$sel_smtpsecure->setSelected($this->getConfig('smtpsecure'));
foreach (['' => $this->i18n('no'), 'ssl' => 'ssl', 'tls' => 'tls'] as $type => $name) {
    $sel_smtpsecure->addOption($name, $type);
}

$sel_encoding = new rex_select();
$sel_encoding->setId('phpmailer-encoding');
$sel_encoding->setName('settings[encoding]');
$sel_encoding->setSize(1);
$sel_encoding->setAttribute('class', 'form-control selectpicker');
$sel_encoding->setSelected($this->getConfig('encoding'));
foreach (['7bit', '8bit', 'binary', 'base64', 'quoted-printable'] as $enc) {
    $sel_encoding->addOption($enc, $enc);
}

$sel_priority = new rex_select();
$sel_priority->setid('phpmailer-priority');
$sel_priority->setName('settings[priority]');
$sel_priority->setSize(1);
$sel_priority->setAttribute('class', 'form-control selectpicker');
$sel_priority->setSelected($this->getConfig('priority'));
foreach ([0 => $this->i18n('disabled'), 1 => $this->i18n('high'), 3 => $this->i18n('normal'), 5 => $this->i18n('low')] as $no => $name) {
    $sel_priority->addOption($name, $no);
}

$sel_log = new rex_select();
$sel_log->setid('phpmailer-log');
$sel_log->setName('settings[log]');
$sel_log->setSize(1);
$sel_log->setAttribute('class', 'form-control selectpicker');
$sel_log->setSelected($this->getConfig('log'));
$sel_log->addOption($this->i18n('log_yes'), 1);
$sel_log->addOption($this->i18n('log_no'), 0);

$sel_debug = new rex_select();
$sel_debug->setid('phpmailer-smtp_debug');
$sel_debug->setName('settings[smtp_debug]');
$sel_debug->setSize(1);
$sel_debug->setAttribute('class', 'form-control selectpicker');
$sel_debug->setSelected($this->getConfig('smtp_debug'));
foreach ([0 => $this->i18n('smtp_debug_0'), 1 => $this->i18n('smtp_debug_1'), 2 => $this->i18n('smtp_debug_2'), 3 => $this->i18n('smtp_debug_3'), 4 => $this->i18n('smtp_debug_4')] as $no => $name) {
    $sel_debug->addOption($name, $no);
}

if ($message != '') {
    echo rex_view::success($message);
}

$content = '';
$content .= '<div class="row">';
$content .= '<div class="col-sm-6">';
$content .= '<fieldset><legend>' . $this->i18n('email_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-fromname">' . $this->i18n('sender_name') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-fromname" type="text" name="settings[fromname]" value="' . rex_escape($this->getConfig('fromname')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-from">' . $this->i18n('sender_email') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-from" type="text" name="settings[from]" placeholder="name@example.tld" value="' . rex_escape($this->getConfig('from')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-confirmto">' . $this->i18n('confirm') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-confirmto" type="text" name="settings[confirmto]" value="' . rex_escape($this->getConfig('confirmto')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-bcc">' . $this->i18n('bcc') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-bcc" type="text" name="settings[bcc]" value="' . rex_escape($this->getConfig('bcc')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-mailer">' . $this->i18n('mailertype') . '</label>';
$n['field'] = $sel_mailer->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';
$content .= '<fieldset id="smtpsettings"><legend>' . $this->i18n('smtp_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-host">' . $this->i18n('host') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-host" placeholder="smtp.example.tld" type="text" name="settings[host]" value="' . rex_escape($this->getConfig('host')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-port">' . $this->i18n('port') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-port" type="text" name="settings[port]" value="' . rex_escape($this->getConfig('port')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label data-toggle="tooltip" title="' . $this->i18n('security_mode_help') . '" for="security_mode">' . rex_escape($this->i18n('security_mode')) . ' <i class="rex-icon fa-question-circle"></i></label>';
$n['field'] = $sel_security_mode->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$content .= '<div id="securetype">';
$n = [];
$n['label'] = '<label for="phpmailer-smtpsecure">' . $this->i18n('smtp_secure') . '</label>';
$n['field'] = $sel_smtpsecure->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '</div>';

$n = [];
$n['label'] = '<label for="phpmailer-smtpauth">' . $this->i18n('smtp_auth') . '</label>';
$n['field'] = $sel_smtpauth->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '<div id="smtpauthlogin">';

$n = [];
$n['label'] = '<label for="phpmailer-username">' . $this->i18n('smtp_username') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-username" type="text" name="settings[username]" value="' . rex_escape($this->getConfig('username')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-password">' . $this->i18n('smtp_password') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-password" type="password" name="settings[password]" value="' . rex_escape($this->getConfig('password')) . '" autocomplete="new-password" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '</div>';

$n = [];
$n['label'] = '<label for="phpmailer-smtp_debug">' . $this->i18n('smtp_debug') . '</label>';
$n['field'] = $sel_debug->get().'<p class="help-block rex-note"> ' . $this->i18n('smtp_debug_info').'</p>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></div>';
$content .= '<fieldset class="col-sm-6"><legend>' . $this->i18n('dispatch_options') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="phpmailer-charset">' . $this->i18n('charset') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-charset" type="text" name="settings[charset]" value="' . rex_escape($this->getConfig('charset')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-wordwrap">' . $this->i18n('wordwrap') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-wordwrap" type="text" name="settings[wordwrap]" value="' . rex_escape($this->getConfig('wordwrap')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-encoding">' . $this->i18n('encoding') . '</label>';
$n['field'] = $sel_encoding->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-priority">' . $this->i18n('priority') . '</label>';
$n['field'] = $sel_priority->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-log">' . $this->i18n('log') . '</label>';
$n['field'] = $sel_log->get();
$n['note'] = rex_i18n::rawMsg('phpmailer_log_info', rex_mailer::logFolder(), '...'.substr(rex_mailer::logFolder(), -30));
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

 $content .= '<legend>' . $this->i18n('check_settings') . '</legend>';

if ($emptymail == '') {
    $content .= '<p>' . $this->i18n('check_settings_inactive') . '</p>';
}

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-testaddress">' . $this->i18n('checkmail_test_address') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-testaddress" type="text" name="settings[test_address]" placeholder="name@example.tld" value="' . rex_escape($this->getConfig('test_address')) . '" />';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

if ($emptymail != '') {
    $content .= '<p>' . $this->i18n('check_settings_intro') . '</p>';
    $content .= '<p><a href="'.rex_url::backendPage('phpmailer/checkmail').'" class="btn btn-save">'.$this->i18n('check_settings_btn').'</a><p>';
}
$content .= '</fieldset></div>';
$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="btn_save" value="' . $this->i18n('save') . '">' . $this->i18n('save') . '</button>';
$formElements[] = $n;
$n = [];
$n['field'] = '<button class="btn btn-reset" type="reset" name="btn_reset" value="' . $this->i18n('reset') . '" data-confirm="' . $this->i18n('reset_info') . '">' . $this->i18n('reset') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('config_settings'), false);
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

