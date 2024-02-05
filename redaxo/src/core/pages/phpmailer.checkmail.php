<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$content = $mailerDebug = '';
$date = new DateTime();
if ('' == rex_config::get('core', 'phpmailer_from') || '' == rex_config::get('core', 'phpmailer_test_address')) {
    $content .= rex_view::error(rex_i18n::msg('phpmailer_checkmail_noadress'));
} else {
    $mail = new rex_mailer();
    $mail->addAddress(rex_config::get('core', 'phpmailer_test_address'));
    $mail->Subject = 'PHPMailer-Test | ' . rex_escape(rex::getServerName()) . ' | ' . date_format($date, 'Y-m-d H:i:s');

    $devider = "\n--------------------------------------------------";
    $securityMode = '';

    if ('smtp' == rex_config::get('core', 'phpmailer_mailer')) {
        $securityMode = rex_config::get('core', 'phpmailer_security_mode');

        $host = "\nHost: " . rex_escape(rex_config::get('core', 'phpmailer_host'));
        $smtpinfo = $host . "\nPort: " . rex_escape(rex_config::get('core', 'phpmailer_port'));
        $smtpinfo .= $devider;

        if (false == $securityMode) {
            $securityMode = 'manual configured ' . rex_config::get('core', 'phpmailer_smtpsecure');
            $securityMode = "\n" . rex_i18n::msg('phpmailer_security_mode') . "\n" . $securityMode . $devider . $smtpinfo;
        } else {
            $securityMode = 'Auto';
            $securityMode = "\n" . rex_i18n::msg('phpmailer_security_mode') . ": \n" . $securityMode . $devider . $host . $devider;
        }
    }

    $mail->Body = rex_i18n::msg('phpmailer_checkmail_greeting') . "\n\n" . rex_i18n::msg('phpmailer_checkmail_text') . ' ' . rex::getServerName();
    $mail->Body .= "\n\nDomain: " . $_SERVER['HTTP_HOST'];

    $mail->Body .= "\nMailer: " . rex_config::get('core', 'phpmailer_mailer') . $devider . $securityMode;
    $mail->Body .= "\n" . rex_i18n::msg('phpmailer_checkmail_domain_note') . "\n" . $devider;
    $mail->Debugoutput = static function ($str) use (&$mailerDebug) {
        $mailerDebug .= date('Y-m-d H:i:s', time()) . ' phpmailer.checkmail.php' . nl2br($str);
    };

    if (!$mail->send()) {
        $alert = '<h2>' . rex_i18n::msg('phpmailer_checkmail_error_headline') . '</h2><hr>';
        $alert .= rex_i18n::msg('phpmailer_checkmail_error') . ': ' . $mail->ErrorInfo;
        $content .= rex_view::error($alert);
    } else {
        $success = '<h2>' . rex_i18n::msg('phpmailer_checkmail_send') . '</h2> ' . rex_escape(rex_config::get('core', 'phpmailer_test_address')) . '<br>' . rex_i18n::msg('phpmailer_checkmail_info');
        $success .= '<br><br><strong>' . rex_i18n::msg('phpmailer_checkmail_info_subject') . '</strong>';
        $content .= rex_view::success($success);
    }
}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('phpmailer_checkmail_headline'));
$fragment->setVar('body', $content . $mailerDebug, false);
$out = $fragment->parse('core/page/section.php');
echo $out;
