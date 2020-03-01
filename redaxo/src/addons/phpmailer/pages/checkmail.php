<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$addon = rex_addon::get('phpmailer');

$content = $smtpinfo = $mailerDebug = '';
$emptymail = true;
$date = new DateTime();
if ('' == $addon->getConfig('from') || '' == $addon->getConfig('test_address')) {
    $emptymail = false;
}
if ($emptymail = true) {
    $mail = new rex_mailer();
    $mail->addAddress($addon->getConfig('test_address'));
    $mail->Subject = 'PHPMailer-Test | ' . rex_escape(rex::getServerName()) . ' | ' . date_format($date, 'Y-m-d H:i:s');

    $devider = "\n--------------------------------------------------";
    $security_mode = '';

    if ('smtp' == $addon->getConfig('mailer')) {
        $security_mode = $addon->getConfig('security_mode');

        $smtpinfo = '';
        $smtpinfo .= "\nHost: " . rex_escape($addon->getConfig('host'));
        $smtpinfo .= "\nPort: " . rex_escape($addon->getConfig('port'));
        $smtpinfo .= $devider;

        if (false == $security_mode) {
            $security_mode = 'manual configured,  ' . $addon->getConfig('smtpsecure');
            $security_mode = "\n".$addon->i18n('security_mode')."\n" . $security_mode . $devider;
        } else {
            $security_mode = 'Auto';
            $security_mode = "\n".$addon->i18n('security_mode').": \n" . $security_mode . $devider;
        }
    }

    $mail->Body = $addon->i18n('checkmail_greeting') ."\n\n" .  $addon->i18n('checkmail_text') .' '. rex::getServerName();
    $mail->Body .= "\n\nDomain: ".  $_SERVER['HTTP_HOST'];

    $mail->Body .= "\nMailer: " . $addon->getConfig('mailer') . $devider.$security_mode;
    $mail->Body .= "\n". $addon->i18n('checkmail_domain_note'). "\n". $devider;
    $mail->Debugoutput = static function ($str, $level) use (&$mailerDebug) {
        $mailerDebug .= date('Y-m-d H:i:s', time()).' '.nl2br($str);
    };

    if (!$mail->send()) {
        $content .= '<div class="alert alert-danger">';
        $content .= '<h2>' . $addon->i18n('checkmail_error_headline') . '</h2><hr>';
        $content .= $addon->i18n('checkmail_error') . ': ' . $mail->ErrorInfo;
        $content .= '</div>';
    } else {
        $content .= '<div class="alert alert-success">';
        $content .= '<strong>' . $addon->i18n('checkmail_send') . '</strong> ' . rex_escape($addon->getConfig('test_address')) . '<br>' . $addon->i18n('checkmail_info');
        $content .= '<br><br><strong>' . $addon->i18n('checkmail_info_subject') . '</strong>';
        $content .= '</div>';
    }
} else {
    $content .= '<div class="alert alert-warning">';
    $content .= $addon->i18n('checkmail_noadress');
    $content .= '</div>';
}
$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('checkmail_headline'));
$fragment->setVar('body', $content.$mailerDebug, false);
$out = $fragment->parse('core/page/section.php');
echo $out;
