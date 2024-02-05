<?php

/**
 * Addon Framework Classes.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$message = '';

if ('' != rex_post('btn_delete_archive', 'string')) {
    if (rex_dir::delete(rex_mailer::logFolder(), true)) {
        echo rex_view::success(rex_i18n::msg('phpmailer_archive_deleted'));
    }
}
if ('' != rex_post('btn_save', 'string') || '' != rex_post('btn_check', 'string')) {
    $settings = rex_post('settings', [
        ['phpmailer_fromname', 'string'],
        ['phpmailer_from', 'string'],
        ['phpmailer_detour_mode', 'boolean'],
        ['phpmailer_confirmto', 'string'],
        ['phpmailer_bcc', 'string'],
        ['phpmailer_mailer', 'string'],
        ['phpmailer_host', 'string'],
        ['phpmailer_port', 'int'],
        ['phpmailer_charset', 'string'],
        ['phpmailer_wordwrap', 'int'],
        ['phpmailer_encoding', 'string'],
        ['phpmailer_username', 'string'],
        ['phpmailer_password', 'string'],
        ['phpmailer_smtpsecure', 'string'],
        ['phpmailer_security_mode', 'boolean'],
        ['phpmailer_smtpauth', 'boolean'],
        ['phpmailer_priority', 'int'],
        ['phpmailer_smtp_debug', 'int'],
        ['phpmailer_test_address', 'string'],
        ['phpmailer_logging', 'int'],
        ['phpmailer_archive', 'boolean'],
    ]);

    if (true == $settings['phpmailer_detour_mode'] && false == rex_validator::factory()->email($settings['phpmailer_test_address'])) {
        $settings['phpmailer_detour_mode'] = false;
        $warning = rex_i18n::msg('phpmailer_detour_warning');
        echo rex_view::warning($warning);
    }

    rex_config::set('core', $settings);

    if ('' != rex_post('btn_check', 'string')) {
        if (false == rex_validator::factory()->email($settings['phpmailer_from']) || false == rex_validator::factory()->email($settings['phpmailer_test_address'])) {
            $warning = rex_i18n::msg('phpmailer_check_settings_not_tested');
            echo rex_view::warning($warning);
        } else {
            rex_response::sendRedirect(rex_url::backendPage('phpmailer/checkmail'));
        }
    }

    $message = rex_i18n::msg('phpmailer_config_saved_successful');
}

$selMailer = new rex_select();
$selMailer->setId('phpmailer-mailer');
$selMailer->setName('settings[phpmailer_mailer]');
$selMailer->setSize(1);
$selMailer->setAttribute('class', 'form-control selectpicker');
$selMailer->setSelected(rex_config::get('core', 'phpmailer_mailer'));
$mta = [];
if (function_exists('mail')) {
    $mta[] = 'mail';
}
$mta[] = 'smtp';
$mta[] = 'sendmail';
foreach ($mta as $type) {
    $selMailer->addOption($type, $type);
}

$selSecurityMode = new rex_select();
$selSecurityMode->setId('security_mode');
$selSecurityMode->setName('settings[phpmailer_security_mode]');
$selSecurityMode->setSize(1);
$selSecurityMode->setAttribute('class', 'form-control selectpicker');
$selSecurityMode->setSelected(rex_config::get('core', 'phpmailer_security_mode'));
foreach ([0 => rex_i18n::msg('phpmailer_security_mode_manual'), 1 => rex_i18n::msg('phpmailer_security_mode_auto')] as $i => $type) {
    $selSecurityMode->addOption($type, $i);
}

$selSmtpauth = new rex_select();
$selSmtpauth->setId('phpmailer-smtpauth');
$selSmtpauth->setName('settings[phpmailer_smtpauth]');
$selSmtpauth->setSize(1);
$selSmtpauth->setAttribute('class', 'form-control selectpicker');
$selSmtpauth->setSelected(rex_config::get('core', 'phpmailer_smtpauth'));
foreach ([0 => rex_i18n::msg('phpmailer_disabled'), 1 => rex_i18n::msg('phpmailer_enabled')] as $i => $type) {
    $selSmtpauth->addOption($type, $i);
}

$selSmtpsecure = new rex_select();
$selSmtpsecure->setId('phpmailer-smtpsecure');
$selSmtpsecure->setName('settings[phpmailer_smtpsecure]');
$selSmtpsecure->setSize(1);
$selSmtpsecure->setAttribute('class', 'form-control selectpicker');
$selSmtpsecure->setSelected(rex_config::get('core', 'phpmailer_smtpsecure'));
foreach (['' => rex_i18n::msg('phpmailer_no'), 'ssl' => 'ssl', 'tls' => 'tls'] as $type => $name) {
    $selSmtpsecure->addOption($name, $type);
}

$selEncoding = new rex_select();
$selEncoding->setId('phpmailer-encoding');
$selEncoding->setName('settings[phpmailer_encoding]');
$selEncoding->setSize(1);
$selEncoding->setAttribute('class', 'form-control selectpicker');
$selEncoding->setSelected(rex_config::get('core', 'phpmailer_encoding'));
foreach (['7bit', '8bit', 'binary', 'base64', 'quoted-printable'] as $enc) {
    $selEncoding->addOption($enc, $enc);
}

$selPriority = new rex_select();
$selPriority->setId('phpmailer-priority');
$selPriority->setName('settings[phpmailer_priority]');
$selPriority->setSize(1);
$selPriority->setAttribute('class', 'form-control selectpicker');
$selPriority->setSelected(rex_config::get('core', 'phpmailer_priority'));
foreach ([0 => rex_i18n::msg('phpmailer_disabled'), 1 => rex_i18n::msg('phpmailer_high'), 3 => rex_i18n::msg('phpmailer_normal'), 5 => rex_i18n::msg('phpmailer_low')] as $no => $name) {
    $selPriority->addOption($name, $no);
}

$selLog = new rex_select();
$selLog->setId('phpmailer-log');
$selLog->setName('settings[phpmailer_logging]');
$selLog->setSize(1);
$selLog->setAttribute('class', 'form-control selectpicker');
$selLog->setSelected(rex_config::get('core', 'phpmailer_logging'));
$selLog->addOption(rex_i18n::msg('phpmailer_log_no'), 0);
$selLog->addOption(rex_i18n::msg('phpmailer_log_errors'), rex_mailer::LOG_ERRORS);
$selLog->addOption(rex_i18n::msg('phpmailer_log_all'), rex_mailer::LOG_ALL);

$selArchive = new rex_select();
$selArchive->setId('phpmailer-archive');
$selArchive->setName('settings[phpmailer_archive]');
$selArchive->setSize(1);
$selArchive->setAttribute('class', 'form-control selectpicker');
$selArchive->setSelected((int) rex_config::get('core', 'phpmailer_archive'));
$selArchive->addOption(rex_i18n::msg('phpmailer_log_no'), 0);
$selArchive->addOption(rex_i18n::msg('phpmailer_log_yes'), 1);

$selDebug = new rex_select();
$selDebug->setId('phpmailer-smtp_debug');
$selDebug->setName('settings[phpmailer_smtp_debug]');
$selDebug->setSize(1);
$selDebug->setAttribute('class', 'form-control selectpicker');
$selDebug->setSelected(rex_config::get('core', 'phpmailer_smtp_debug'));
foreach ([0 => rex_i18n::msg('phpmailer_smtp_debug_0'), 1 => rex_i18n::msg('phpmailer_smtp_debug_1'), 2 => rex_i18n::msg('phpmailer_smtp_debug_2'), 3 => rex_i18n::msg('phpmailer_smtp_debug_3'), 4 => rex_i18n::msg('phpmailer_smtp_debug_4')] as $no => $name) {
    $selDebug->addOption($name, $no);
}

if ('' != $message) {
    echo rex_view::success($message);
}

$content = '';
$content .= '<div class="row">';
$content .= '<div class="col-sm-6">';
$content .= '<fieldset><legend>' . rex_i18n::msg('phpmailer_email_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-fromname">' . rex_i18n::msg('phpmailer_sender_name') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-fromname" type="text" name="settings[phpmailer_fromname]" value="' . rex_escape(rex_config::get('core', 'phpmailer_fromname')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-from">' . rex_i18n::msg('phpmailer_sender_email') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-from" type="email" name="settings[phpmailer_from]" placeholder="name@example.tld" value="' . rex_escape(rex_config::get('core', 'phpmailer_from')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-testaddress">' . rex_i18n::msg('phpmailer_checkmail_test_address') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-testaddress" type="email" name="settings[phpmailer_test_address]" placeholder="test@example.tld" value="' . rex_escape(rex_config::get('core', 'phpmailer_test_address')) . '" />';
$formElements[] = $n;

$selDetourMode = new rex_select();
$selDetourMode->setId('phpmailer-detour-mode');
$selDetourMode->setName('settings[phpmailer_detour_mode]');
$selDetourMode->setSize(1);
$selDetourMode->setAttribute('class', 'form-control selectpicker');

$selDetourMode->setSelected(rex_config::get('core', 'phpmailer_detour_mode') ?: 0);
foreach ([rex_i18n::msg('phpmailer_disabled'), rex_i18n::msg('phpmailer_enabled')] as $key => $value) {
    $selDetourMode->addOption($value, $key);
}

$detourModeLabelClass = rex_config::get('core', 'phpmailer_detour_mode') ? 'text-danger' : '';

$n = [];
$n['label'] = '<label for="phpmailer-detour-mode" class="' . $detourModeLabelClass . '">' . rex_i18n::msg('phpmailer_detour_email_redirect') . '</label>';
$n['field'] = $selDetourMode->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-confirmto">' . rex_i18n::msg('phpmailer_confirm') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-confirmto" type="email" name="settings[phpmailer_confirmto]" placeholder="confirm@example.tld" value="' . rex_escape(rex_config::get('core', 'phpmailer_confirmto')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-bcc">' . rex_i18n::msg('phpmailer_bcc') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-bcc" type="email" name="settings[phpmailer_bcc]" placeholder="bcc@example.tld" value="' . rex_escape(rex_config::get('core', 'phpmailer_bcc')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-mailer">' . rex_i18n::msg('phpmailer_mailertype') . '</label>';
$n['field'] = $selMailer->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset>';
$content .= '<fieldset id="smtpsettings"><legend>' . rex_i18n::msg('phpmailer_smtp_options') . '</legend>';

$formElements = [];
$n = [];
$n['label'] = '<label for="phpmailer-host">' . rex_i18n::msg('phpmailer_host') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-host" placeholder="smtp.example.tld" type="text" name="settings[phpmailer_host]" value="' . rex_escape(rex_config::get('core', 'phpmailer_host')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-port">' . rex_i18n::msg('phpmailer_port') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-port" type="number" name="settings[phpmailer_port]" value="' . rex_escape(rex_config::get('core', 'phpmailer_port')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label data-toggle="tooltip" title="' . rex_i18n::msg('phpmailer_security_mode_help') . '" for="security_mode">' . rex_escape(rex_i18n::msg('phpmailer_security_mode')) . ' <i class="rex-icon fa-question-circle"></i></label>';
$n['field'] = $selSecurityMode->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$content .= '<div id="securetype">';
$n = [];
$n['label'] = '<label for="phpmailer-smtpsecure">' . rex_i18n::msg('phpmailer_smtp_secure') . '</label>';
$n['field'] = $selSmtpsecure->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '</div>';

$n = [];
$n['label'] = '<label for="phpmailer-smtpauth">' . rex_i18n::msg('phpmailer_smtp_auth') . '</label>';
$n['field'] = $selSmtpauth->get();
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '<div id="smtpauthlogin">';

$n = [];
$n['label'] = '<label for="phpmailer-username">' . rex_i18n::msg('phpmailer_smtp_username') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-username" type="text" name="settings[phpmailer_username]" value="' . rex_escape(rex_config::get('core', 'phpmailer_username')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-password">' . rex_i18n::msg('smtp_password') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-password" type="password" name="settings[phpmailer_password]" value="' . rex_escape(rex_config::get('core', 'phpmailer_password')) . '" autocomplete="new-password" />';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');
$formElements = [];
$content .= '</div>';

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></div>';
$content .= '<fieldset class="col-sm-6"><legend>' . rex_i18n::msg('phpmailer_dispatch_options') . '</legend>';

$formElements = [];

$n = [];
$n['label'] = '<label for="phpmailer-charset">' . rex_i18n::msg('phpmailer_charset') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-charset" type="text" name="settings[phpmailer_charset]" value="' . rex_escape(rex_config::get('core', 'phpmailer_charset')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-wordwrap">' . rex_i18n::msg('phpmailer_wordwrap') . '</label>';
$n['field'] = '<input class="form-control" id="phpmailer-wordwrap" type="number" name="settings[phpmailer_wordwrap]" value="' . rex_escape(rex_config::get('core', 'phpmailer_wordwrap')) . '" />';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-encoding">' . rex_i18n::msg('phpmailer_encoding') . '</label>';
$n['field'] = $selEncoding->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-priority">' . rex_i18n::msg('phpmailer_priority') . '</label>';
$n['field'] = $selPriority->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-smtp_debug">' . rex_i18n::msg('phpmailer_smtp_debug') . '</label>';
$n['field'] = $selDebug->get() . '<p class="help-block rex-note"> ' . rex_i18n::msg('phpmailer_smtp_debug_info') . '</p>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-log">' . rex_i18n::msg('phpmailer_logging') . '</label>';
$n['field'] = $selLog->get();
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="phpmailer-archive">' . rex_i18n::msg('phpmailer_archive') . '</label>';
$n['field'] = $selArchive->get();
$n['note'] = rex_i18n::rawMsg('phpmailer_archive_info', rex_mailer::logFolder(), '...' . substr(rex_mailer::logFolder(), -30));
$formElements[] = $n;

if (is_dir(rex_mailer::logFolder())) {
    $n = [];
    $n['field'] = '<button data-confirm="' . rex_i18n::msg('phpmailer_archive_delete_confirm') . '" class="btn btn-danger pull-right" type="submit" name="btn_delete_archive" value="' . rex_i18n::msg('phpmailer_archive_delete') . '">' . rex_i18n::msg('phpmailer_archive_delete') . '</button>';
    $formElements[] = $n;
}

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$content .= '</fieldset></div>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-reset pull-right" type="reset" name="btn_reset" value="' . rex_i18n::msg('phpmailer_reset') . '" data-confirm="' . rex_i18n::msg('phpmailer_reset_info') . '">' . rex_i18n::msg('phpmailer_reset') . '</button>';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-save pull-right" type="submit" name="btn_check" value="' . rex_i18n::msg('phpmailer_check_settings_btn') . '">' . rex_i18n::msg('phpmailer_check_settings_btn') . '</button>';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-save pull-right" type="submit" name="btn_save" value="' . rex_i18n::msg('phpmailer_save') . '">' . rex_i18n::msg('phpmailer_save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('phpmailer_config_settings'), false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');
echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $content . '
    </form>';
?>
<script nonce="<?= rex_response::getNonce() ?>">
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

