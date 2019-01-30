<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 */

$myaddon = rex_addon::get('phpmailer');

$content = $smtpinfo = '';
$emptymail = true;
$date = new DateTime();
if ($myaddon->getConfig('from') == '' || $myaddon->getConfig('test_address') == '') {
    $emptymail = false;
}
if ($emptymail = true) {
    $mail = new rex_mailer();
    $mail->addAddress($myaddon->getConfig('test_address'));
    $mail->Subject = 'PHPMailer-Test | ' . rex_escape(rex::getServerName()) . ' | ' . date_format($date, 'Y-m-d H:i:s');

    $devider = "\n--------------------------------------------------";
    $security_mode = '';

    if ($myaddon->getConfig('mailer') == 'smtp') {
        $security_mode = $myaddon->getConfig('security_mode');

        $smtpinfo = '';
        $smtpinfo .= "\nHost: " . rex_escape($myaddon->getConfig('host'));
        $smtpinfo .= "\nPort: " . rex_escape($myaddon->getConfig('port'));
        $smtpinfo .= $devider;

        if ($security_mode == false) {
            $security_mode = 'manual configured,  ' . $myaddon->getConfig('smtpsecure');
            $security_mode = "\n".$myaddon->i18n('security_mode')."\n" . $security_mode . $devider;
        } else {
            $security_mode = 'Auto';
            $security_mode = "\n".$myaddon->i18n('security_mode').": \n" . $security_mode . $devider;
        }
    }

    $mail->Body = $myaddon->i18n('checkmail_greeting') ."\n\n" .  $myaddon->i18n('checkmail_text') .' '. rex::getServerName();
    $mail->Body .= "\n\nDomain: ".  $_SERVER['HTTP_HOST'];

    $mail->Body .= "\nMailer: " . $myaddon->getConfig('mailer') . $devider.$security_mode;
    $mail->Body .= "\n". $myaddon->i18n('checkmail_domain_note'). "\n". $devider;

    if (!$mail->send()) {
        $content .= '<div class="alert alert-danger">';
        $content .= '<h2>' . $myaddon->i18n('checkmail_error_headline') . '</h2><hr>';
        $content .= $myaddon->i18n('checkmail_error') . ': ' . $mail->ErrorInfo;
        $content .= '</div>';
    } else {
        $content .= '<div class="alert alert-success">';
        $content .= '<strong>' . $myaddon->i18n('checkmail_send') . '</strong> ' . rex_escape($myaddon->getConfig('test_address')) . '<br>' . $myaddon->i18n('checkmail_info');
        $content .= '<br><br><strong>' . $myaddon->i18n('checkmail_info_subject') . '</strong>';
        $content .= '</div>';
    }
} else {
    $content .= '<div class="alert alert-warning">';
    $content .= $myaddon->i18n('checkmail_noadress');
    $content .= '</div>';
}
$fragment = new rex_fragment();
$fragment->setVar('title', $myaddon->i18n('checkmail_headline'));
$fragment->setVar('body', $content, false);
$out = $fragment->parse('core/page/section.php');
echo $out;
