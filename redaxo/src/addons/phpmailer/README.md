# PHPMailer

- [Allgemeines](#allgemeines)
- [Beispiele](#beispiele)
- [E-Mail-Benachrichtigung bei Fehlern](#errormail)
- [Tipps](#tipps)
    - [Spam-Blocker](#spam-blocker)
    - [Verwendung bei selbstsignierten Zertifikaten](#zertifikate)
    - [Verschlüsselung: Automatische TLS-Verbindung](#autotls)
    - [Senden über unterschiedliche Domains](#multidomain)

<a name="ueber"></a>

## Allgemeines
Das PHPMailer-AddOn ermöglicht den Versand von E-Mails. Zusätzlich kann phpmailer den Administrator bei aufgetretenen Fehlern per E-Mail benachrichtigen. 

**Unterstützt werden folgende Sendeverfahren**
- php mail()
- sendmail
- SMTP/SMTPS
- SMTP/SMTPS-Auth

Der Aufruf erfolgt über die Klasse `rex_mailer`. Dabei werden die nachfolgend beschriebenen und in der Konfiguration hinterlegten Einstellungen berücksichtigt.

Die Werte der Konfiguration können in AddOns oder Modulen leicht überschrieben werden, siehe [Beispiele](#beispiele).

> Liefert ein AddOn oder Modul keine eigenen Einstellungen für das Versenden der E-Mails, gelten die Sende-Einstellungen im PHPMailer-AddOn. 

Weitere Informationen zur Verwendung von PHPMailer unter: [https://github.com/PHPMailer/PHPMailer/wiki/Tutorial](https://github.com/PHPMailer/PHPMailer/wiki/Tutorial)

> **Hinweis:** Eine Test-Mail kann nach dem Speichern der Einstellungen verschickt werden. Hierzu müssen unbedingt Absender- und Test-Adresse festgelegt werden.

<a name="beispiele"></a>
## PHPMailer Code-Beispiele


### 1. Beispiel

E-Mail an einen bestimmten Empfänger senden.

```php
<?php
  // PHPMailer-Instanz
  $mail = new rex_mailer();

  //Absenderadresse überschreiben
 // $mail->From = "absender@domain.tld";

  //Absendername überschreiben
  // $mail->FromName = "Vorname Nachname";

  // Antwortadresse festlegen
  // $mail->addReplyTo("username@domain.com", "Software Simian");

  // Empfänger
  $mail->addAddress("empfaenger@domain.tld");

  // Empfänger als CC hinzufügen - Weitere anlegen wenn mehrere erwünscht
  // $mail->addCC("empfaenger2@domain.tld);

  // Empfänger als BCC hinzufügen - Weitere anlegen wenn mehrere erwünscht
  // $mail->addBCC("empfaenger3@domain.tld");

  //Betreff der E-Mail
  $mail->Subject = "PHPMailer-Test";

  //Text der EMail setzen
  $mail->Body = "Hi \n\n this mail was sent by PHPMailer!";

  //Überprüfen ob E-Mail gesendet wurde
  if(!$mail->send())
  {
     echo "An error occurred";
     echo "Error: " . $mail->ErrorInfo;
  }
  else
  {
     echo "E-Mail has been sent";
  }
```


### 2. Beispiel

E-Mail an einen Empfängerkreis senden, der aus der Datenbank ausgelesen wird.


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

```
<a name="errormail"></a>

## E-Mail-Benachrichtigung bei Fehlern

PHPMAiler versendet einen Auszug des system.log, wenn es Exceptions, Errors und eigene logevents findet. 
Der Check und ggf. Zusendung erfolgt in festen Intervallen, die in den Systemeinstellungen definiert werden können. Empfänger ist die im System hinterlegte Fehleradresse. 

Eigene Events können den Versand ebenso auslösen dazu kann man im Log den Event als Typ: logevent ablegen. 
`rex_logger::factory()->log('logevent', 'Mein Text zum Event');`


<a name="tipps"></a>

## Tipps

<a name="autotls"></a>
### Verschlüsselung: Automatische TLS-Verbindung

PHPMailer prüft ob der angegebene Server TLS unterstützt und baut eine verschlüsselte TLS-Verbindung auf. Erlaubt der Server keine Verschlüsselung, wird eine unsichere Verbindung aufgebaut. Sollte es zu Problemen beim Versand kommen, liegt es häufig daran, dass das hinterlegte Zertifikat nicht mit dem angegebenen Host übereinstimmt oder kein gültiges Zertifikat gefunden wurde. Durch Ändern der Verschlüsselung auf "manuelle Auswahl" kann die automatische Erkennung deaktiviert werden und die Verschlüsselung manuell gewählt werden. 

> Diese Einstellung kann zu unsicheren Verbindungen führen, sollte keine TLS-Unterstützung gefunden werden. 

<a name="spam-blocker"></a>
### Spam-Blocker

- Der Server, der die E-Mails versendet, sollte möglichst per SPF-Eintrag für die verwendete E-Mail-Domain als autorisierter Server im DNS hinterlegt sein.

- Prioritäts-Einstellungen können zu einem Spam-Blocking führen.

- Große E-Mail-Verteiler sollten möglichst in kleiner Zahl und nicht als CC verschickt werden.

<a name="zertifikate"></a>
### Verwendung bei selbstsignierten Zertifikaten

Per Default wird der Peer verifiziert. Dies kann ggf. zu Problemen führen. Die nachfolgenden Einstellungen helfen, dieses Problem zu umgehen.

```php
<?php

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

<a name="multidomain"></a>
### Senden über unterschiedliche Domains 

Werden E-Mails über unterschiedliche Absender-Domains verschickt, sollte der SPF-Eintrag der Absender-Domain in den DNS-Einstellungen

- den Webserver (bei sendmail und mail) 
- oder den angegebenen SMTP(S)-Server 

als erlaubte Adressen beinhalten. 

z.B. `a:meine-domain.tld ip4:XXX.XXX.XXX.XXX`

Somit wird sichergestellt, das PHPMailer E-Mails unter der angegebenen Domain versenden kann und die Mail nicht als SPAM deklariert wird.  

Hierzu ggf. den Registrar oder DNS-Administrator kontaktieren. 

