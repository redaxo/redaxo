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
        ['smtpauth', 'boolean'],
        ['priority', 'int'],
        ['smtp_debug', 'int'],
    ]));

    $message = $this->i18n('config_saved_successful');
}

$sel_mailer = new rex_select();
$sel_mailer->setId('phpmailer-mailer');
$sel_mailer->setName('settings[mailer]');
$sel_mailer->setSize(1);
$sel_mailer->setAttribute('class', 'form-control');
$sel_mailer->setSelected($this->getConfig('mailer'));
foreach (['mail', 'sendmail', 'smtp'] as $type) {
    $sel_mailer->addOption($type, $type);
}

$sel_smtpauth = new rex_select();
$sel_smtpauth->setId('phpmailer-smtpauth');
$sel_smtpauth->setName('settings[smtpauth]');
$sel_smtpauth->setSize(1);
$sel_smtpauth->setAttribute('class', 'form-control');
$sel_smtpauth->setSelected($this->getConfig('smtpauth'));
foreach ([0 => 'false', 1 => 'true'] as $i => $type) {
    $sel_smtpauth->addOption($type, $i);
}

$sel_smtpsecure = new rex_select();
$sel_smtpsecure->setId('phpmailer-smtpsecure');
$sel_smtpsecure->setName('settings[smtpsecure]');
$sel_smtpsecure->setSize(1);
$sel_smtpsecure->setAttribute('class', 'form-control');
$sel_smtpsecure->setSelected($this->getConfig('smtpsecure'));
foreach (['' => $this->i18n('no'), 'ssl' => 'ssl', 'tls' => 'tls'] as $type => $name) {
    $sel_smtpsecure->addOption($name, $type);
}

$sel_encoding = new rex_select();
$sel_encoding->setId('phpmailer-encoding');
$sel_encoding->setName('settings[encoding]');
$sel_encoding->setSize(1);
$sel_encoding->setAttribute('class', 'form-control');
$sel_encoding->setSelected($this->getConfig('encoding'));
foreach (['7bit', '8bit', 'binary', 'base64', 'quoted-printable'] as $enc) {
    $sel_encoding->addOption($enc, $enc);
}

$sel_priority = new rex_select();
$sel_priority->setid('phpmailer-priority');
$sel_priority->setName('settings[priority]');
$sel_priority->setSize(1);
$sel_priority->setAttribute('class', 'form-control');
$sel_priority->setSelected($this->getConfig('priority'));
foreach ([0 => $this->i18n('disabled'), 1 => $this->i18n('high'), 3 => $this->i18n('normal'), 5 => $this->i18n('low')] as $no => $name) {
    $sel_priority->addOption($name, $no);
}
$sel_debug = new rex_select();
$sel_debug->setid('phpmailer-smtp_debug');
$sel_debug->setName('settings[smtp_debug]');
$sel_debug->setSize(1);
$sel_debug->setAttribute('class', 'form-control');
$sel_debug->setSelected($this->getConfig('smtp_debug'));
foreach ([0 => $this->i18n('smtp_debug_0'), 1 => $this->i18n('smtp_debug_1'), 2 => $this->i18n('smtp_debug_2'), 3 => $this->i18n('smtp_debug_3'), 4 => $this->i18n('smtp_debug_4')] as $no => $name) {
    $sel_debug->addOption($name, $no);
}

if ($message != '') {
    echo rex_view::success($message);
}

$content = '';

$content .= '<fieldset><legend>' . $this->i18n('email_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-fromname">' . $this->i18n('sender_name') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-fromname" type="text" name="settings[fromname]" value="' . $this->getConfig('fromname') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-from">' . $this->i18n('sender_email') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-from" type="text" name="settings[from]" value="' . $this->getConfig('from') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-confirmto">' . $this->i18n('confirm') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-confirmto" type="text" name="settings[confirmto]" value="' . $this->getConfig('confirmto') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-bcc">' . $this->i18n('bcc') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-bcc" type="text" name="settings[bcc]" value="' . $this->getConfig('bcc') . '" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset><fieldset><legend>' . $this->i18n('dispatch_options') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="phpmailer-mailer">' . $this->i18n('mailertype') . '</label>';
$n['field'] = $sel_mailer->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-host">' . $this->i18n('host') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-host" type="text" name="settings[host]" value="' . $this->getConfig('host') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-port">' . $this->i18n('port') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-port" type="text" name="settings[port]" value="' . $this->getConfig('port') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-charset">' . $this->i18n('charset') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-charset" type="text" name="settings[charset]" value="' . $this->getConfig('charset') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-wordwrap">' . $this->i18n('wordwrap') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-wordwrap" type="text" name="settings[wordwrap]" value="' . $this->getConfig('wordwrap') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-encoding">' . $this->i18n('encoding') . '</label>';
$n['field'] = $sel_encoding->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-priority">' . $this->i18n('priority') . '</label>';
$n['field'] = $sel_priority->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset><fieldset><legend>' . $this->i18n('smtp_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-smtpsecure">' . $this->i18n('smtp_secure') . '</label>';
$n['field'] = $sel_smtpsecure->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-smtpauth">' . $this->i18n('smtp_auth') . '</label>';
$n['field'] = $sel_smtpauth->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-username">' . $this->i18n('smtp_username') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-username" type="text" name="settings[username]" value="' . $this->getConfig('username') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-password">' . $this->i18n('smtp_password') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-password" type="text" name="settings[password]" value="' . $this->getConfig('password') . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-smtp_debug">' . $this->i18n('smtp_debug') . '</label>';
$n['field'] = $sel_debug->get();
$formElements[] = $n;


$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';

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
