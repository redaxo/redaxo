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
$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('checkmail_headline'));
$fragment->setVar('body', $body, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
if ($this->getConfig('from')!='')
{
  // PHPMailer-Instanz 
  $mail = new rex_mailer();
 
  //Absenderadresse überschreiben
 // $mail->From = "absender@domain.tld";
  
  //Absendername überschreiben
  // $mail->FromName = "Vorname Nachname";
  
  // Antwortadresse festlegen 
  // $mail->AddReplyTo("username@domain.com", "Software Simian");
  
  // Empfänger 
  $mail->AddAddress($this->getConfig('from'));
  
  // Empfänger als CC hinzufügen - Weitere anlegen wenn mehrere erwünscht
  // $mail->AddCC("empfaenger2@domain.tld);
  
  // Empfänger als BCC hinzufügen - Weitere anlegen wenn mehrere erwünscht
  // $mail->AddBCC("empfaenger3@domain.tld");
 
  //Betreff der E-Mail 
  $mail->Subject = "PHPMailer-Test";
 
  //Text der EMail setzen
  $mail->Body = "Hi \n\n this mail was sent by PHPMailer!";
  
  //Überprüfen ob E-Mail gesendet wurde
  if(!$mail->Send())
  {
     echo '<div class="alert alert-danger">';
     echo '<h2>'.$this->i18n('checkmail_error_headline') . '</h2><hr>';
     echo $this->i18n('checkmail_error') . ': ' . $mail->ErrorInfo;
     echo '</div>';
  }
  else
  {
     echo '<div class="alert alert-success">';
     echo '<strong>'.$this->i18n('checkmail_send') . '</strong> ' . $this->getConfig('from') . '<br>' . $this->i18n('checkmail_info');
     echo '</div>';
  }
  
}
else {
    echo '<div class="alert alert-warning">';
	echo $this->i18n('checkmail_noadress');
	echo '</div>';	
}
