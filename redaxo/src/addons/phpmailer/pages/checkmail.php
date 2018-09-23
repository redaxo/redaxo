<?php
/**
 * PHPMailer Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */
$content = $smtpinfo = '';
$emptymail = true;
$date = new DateTime();
if ($this->getConfig('from') == '' || $this->getConfig('test_address') == '') {
    $emptymail = false;
}
if ($emptymail = true) {
    $mail = new rex_mailer();
    $mail->addAddress($this->getConfig('test_address'));
    $mail->Subject = 'PHPMailer-Test | ' . rex_escape(rex::getServerName()) . ' | ' . date_format($date, 'Y-m-d H:i:s');

    $devider = "\n--------------------------------------------------";
    $security_mode = '';

    if ($this->getConfig('mailer') == 'smtp') {
        $security_mode = $this->getConfig('security_mode');

        $smtpinfo = '';
        $smtpinfo .= "\nHost: " . rex_escape($this->getConfig('host'));
        $smtpinfo .= "\nPort: " . rex_escape($this->getConfig('port'));
        $smtpinfo .= $devider;

        if ($security_mode == false) {
            $security_mode = 'manual configured,  ' . $this->getConfig('smtpsecure');
            $security_mode = "\n".$this->i18n('security_mode')."\n" . $security_mode . $devider;
        } else {
            $security_mode = 'Auto';
            $security_mode = "\n".$this->i18n('security_mode').": \n" . $security_mode . $devider;
        }
    }

    $mail->Body = $this->i18n('checkmail_greeting') ."\n\n" .  $this->i18n('checkmail_text') .' '. rex::getServerName();
    $mail->Body .= "\n\nDomain: ".  $_SERVER['HTTP_HOST'];

    $mail->Body .= "\nMailer: " . $this->getConfig('mailer') . $devider.$security_mode;
    $mail->Body .= "\n". $this->i18n('checkmail_domain_note'). "\n". $devider;

    if (!$mail->send()) {
        $content .= '<div class="alert alert-danger">';
        $content .= '<h2>' . $this->i18n('checkmail_error_headline') . '</h2><hr>';
        $content .= $this->i18n('checkmail_error') . ': ' . $mail->ErrorInfo;
        $content .= '</div>';
    } else {
        $content .= '<div class="alert alert-success">';
        $content .= '<strong>' . $this->i18n('checkmail_send') . '</strong> ' . rex_escape($this->getConfig('test_address')) . '<br>' . $this->i18n('checkmail_info');
        $content .= '<br><br><strong>' . $this->i18n('checkmail_info_subject') . '</strong>';
        $content .= '</div>';
    }
} else {
    $content .= '<div class="alert alert-warning">';
    $content .= $this->i18n('checkmail_noadress');
    $content .= '</div>';
}
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('checkmail_headline'));
$fragment->setVar('body', $content, false);
$out = $fragment->parse('core/page/section.php');
echo $out;
