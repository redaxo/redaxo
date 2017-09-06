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
$emptymail = '1';

if ($this->getConfig('from') == '')
{
	$emptymail ='';
}
if ($this->getConfig('test_adress') == '') 
{
    $emptymail =''; 	
}

if ($emptymail!='')
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
  $mail->AddAddress($this->getConfig('test_adress'));
  
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
     $content .= '<div class="alert alert-danger">';
     $content .=  '<h2>'.$this->i18n('checkmail_error_headline') . '</h2><hr>';
     $content .=  $this->i18n('checkmail_error') . ': ' . $mail->ErrorInfo;
     $content .=  '</div>';
  }
  else
  {
  	
     $content .=  '<div class="alert alert-success">';
     $content .=  '<strong>'.$this->i18n('checkmail_send') . '</strong> ' . $this->getConfig('from') . '<br>' . $this->i18n('checkmail_info');
     $content .=  '</div>';
  }
  
}

else {
        $content .=  '<div class="alert alert-warning">';
	$content .=  $this->i18n('checkmail_noadress');
	$content .=  '</div>';
	
}

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('checkmail_headline'));
$fragment->setVar('body', $content, false);
$out = $fragment->parse('core/page/section.php');
echo $out;


