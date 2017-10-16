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
$content = '';
$emptymail = '1';

if ($this->getConfig('from') == '' || $this->getConfig('test_address') == '') {
    $emptymail = '';
}
if ($emptymail != '') {
    $mail = new rex_mailer();
    $mail->addAddress($this->getConfig('test_address'));
    $mail->Subject = 'PHPMailer-Test';
    $mail->Body = "Hi \n\n this mail was sent by PHPMailer!";
    if (!$mail->send()) {
        $content .= '<div class="alert alert-danger">';
        $content .= '<h2>'.$this->i18n('checkmail_error_headline') . '</h2><hr>';
        $content .= $this->i18n('checkmail_error') . ': ' . $mail->ErrorInfo;
        $content .= '</div>';
    } else {
        $content .= '<div class="alert alert-success">';
        $content .= '<strong>'.$this->i18n('checkmail_send') . '</strong> ' . $this->getConfig('test_address') . '<br>' . $this->i18n('checkmail_info');
        $content .= '<br><br><strong>'.$this->i18n('checkmail_info_subject').'</strong>';
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
