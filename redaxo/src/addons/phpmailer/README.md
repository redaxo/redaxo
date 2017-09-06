## Inhalt
- [Über](#ueber)
- [Beispiele](#beispiele)
- [Tipps](#tipps)

<a name="ueber"></a>

## Über
PHPMailer ermöglicht den Versand von E-Mails. Das AddOn stellt die Class PHPMailer zur Verfügung. 

Der Aufruf erfolgt über die Class rex_mailer. Dabei werden die hier in der Konfiguration hintergeten Einstellungen berücksichtigt. 

Die Werte können jedoch von Fall zu Fall überschrieben überschrieben werden. (z.B. für unterschiedliche Empfänger)

Weitere Informationen zur Verwendung von PHPMailer unter:  https://github.com/PHPMailer/PHPMailer/wiki/Tutorial

<a name="beispiele"></a>
## PHPMailer Code-Beispiele


### 1. Beispiel

```php 
<?php 
  // PHPMailer-Instanz 
  $mail = new rex_mailer();
 
  //Absenderadresse überschreiben
 // $mail->From = "absender@domain.tld";
  
  //Absendername überschreiben
  // $mail->FromName = "Vorname Nachname";
  
  // Antwortadresse festlegen 
  // $mail->AddReplyTo("username@domain.com", "Software Simian");
  
  // Empfänger 
  $mail->AddAddress("empfaenger@domain.tld");
  
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
     echo "An error occurred";
     echo "Error: " . $mail->ErrorInfo;
  }
  else
  {
     echo "E-Mail has been sent";
  }
?>
```


### 2. Beispiel


```php

<?php

$mail = new rex_mailer();
$sql = rex_sql::factory();

$query = "SELECT full_name, email, photo FROM employee WHERE id= ?";
$sql->setQuery($query, array($id));

foreach($sql as $row)
{
    // HTML body
    $body  = "Hello <font size=\"4\">" . $row->getValue("full_name") . "</font>, <p>";
    $body .= "<i>Your</i> personal photograph to this message.<p>";
    $body .= "Sincerely, <br />";
    $body .= "phpmailer List manager";

    // Plain text body (for mail clients that cannot read HTML)
    $text_body  = "Hello " . $row->getValue("full_name") . ", \n\n";
    $text_body .= "Your personal photograph to this message.\n\n";
    $text_body .= "Sincerely, \n";
    $text_body .= "phpmailer List manager";

    $mail->Body    = $body;
    $mail->Subject = "PHPMailer-Test";
    $mail->AltBody = $text_body;
    $mail->AddAddress($row->getValue("email"), $row->getValue("full_name"));
    $mail->AddStringAttachment($sql->getValue("photo"), "YourPhoto.jpg");

    if(!$mail->Send())
        echo "There has been a mail error sending to " . $row->getValue("email") . "<br>";

    // Clear all addresses and attachments for next loop
    $mail->ClearAddresses();
    $mail->ClearAttachments();
}

?>
```

<a name="tipps"></a>

## Tipps

### Spam-Blocker

- Der Server, der die E-Mails versendet sollte möglichst per SPF-Eintrag für die verwendete E-Mail-Doman als autorisierter Server im DNS hinterlegt sein. 

- Prioritäts-Einstellungen können zu einem Spam-Blocking führen. 

- Große E-Mail-Verteiler sollten möglichst in kleiner Zahl, und nicht als CC verschickt werden. 


### Verwendung bei selbstsignierten Zertifikaten

Per Default wird der Peer verifiziert. Dies kann ggf. zu Prpblemen führen. Die nachfolgenden Einstellungen helfen das zu umgehen.  

```php
$mail = new rex_mailer();
$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ),
    'tls' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ),
);
```