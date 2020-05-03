# PHPMailer

## About
The PHPMailer-AddOn allows you to send e-mails. In addition, PHPMailer can notify the administrator via e-mail if errors occur in the system. 

**The following transmission methods are supported**
- php mail()
- sendmail 
- SMTP/SMTPS
- SMTP/SMTPS-Auth

The call is made via the class `rex_mailer`. The settings stored in the configuration are taken into account.

The configuration values can easily be overwritten by AddOns or modules, see [Examples](#examples).

> If an AddOn or module does not provide its own settings for sending e-mails, the send settings in the PHPMailer AddOn are applied. 

For more information about using PHPMailer, see: [https://github.com/PHPMailer/PHPMailer/wiki/Tutorial](https://github.com/PHPMailer/PHPMailer/wiki/Tutorial)

> **Note:** A test mail can be sent with the button **Save and test**. For this purpose, the sender and test address must be specified.

## PHPMailer Code Examples


### 1. Beispiel

Send an e-mail to a specific recipient.

```php
<?php
  // PHPMailer Instance
  $mail = new rex_mailer();

  //Overwrite sender address
 // $mail->From = "sender@domain.tld";

  //Overwrite sender name
  // $mail->FromName = "Firstname Surname";

  // Define reply address
  // $mail->addReplyTo("username@domain.com", "Software Simian");

  //  Recipient
  $mail->addAddress("recipient@domain.tld");

  // Add recipient as CC - Create more if you want more
  // $mail->addCC("recipient2@domain.tld);

  // Add recipient as BCC - Create more if more are desired
  // $mail->addBCC("recipient2@domain.tld");

  //Subject of the e-mail
  $mail->Subject = "PHPMailer-Test";

  //text of the email
  $mail->Body = "Hi \n\n this mail was sent by PHPMailer!";

  //Check if email has been sent
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


### 2. Example

Send an e-mail to a group of recipients that is read from the database.

```php

<?php

$mail = new rex_mailer();
$sql = rex_sql::factory();

$query = "SELECT full_name, email, photo FROM employee WHERE id= ?";
$sql->setQuery($query, array($id));

foreach($sql as $row)
{
    // HTML body
    $body  = "Hello " . $row->getValue("full_name") . ",";
    $body .= "<p><i>Your</i> personal photograph to this message.<p>";
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

## E-mail notification in case of errors

PHPMAiler sends an excerpt of system.log when it finds exceptions, errors, and custom log events. 
The check and if necessary the sending are done in fixed intervals, which can be defined in the system settings. The recipient is the error address stored in the system settings. 

Your own events can also trigger the dispatch. You can store this event as type: logevent. 

`rex_logger::factory()->log('logevent', 'Mein Text zum Event');`

## SMTP-Debug

Setting the debug mode leads to different outputs:

### Client protocol

Output messages sent by the client.

### Server and client protocol

as Client protocol, plus responses received from the server (this is the most useful setting).

### Connection protocol

as Server and client protocol, plus more information about the initial connection - this level can help diagnose STARTTLS failures

### Lowlevel protocol

as Connection protocol, plus even lower-level information, very verbose, don't use for debugging SMTP, only low-level problems.

Most of the time you don't need a level over **server and client protocol**, unless there are difficulties with the connection. The output will usually be more extensive and harder to read.

### Email log

The Email log can be found under 'System' > 'Log files' > 'PHPMailer'. The logging can be set in the settings of the PHPMailer addon at 3 levels. 

- No: No log will be created.
- Log only errors: Only errors will be logged. 
- Log all transactions: All transmissions are logged 

The log provides information about date/time, sender, recipient, subject and message. It can be ecleared via 'Clear log file'. 

The log is stored under `/redaxo/data/log/mail.log`.

### Email archiving 

If Email archiving is switched on, all emails in the folder '/redaxo/data/addons/phpmailer/mail_log' are archived chronologically by year and month in subfolders. Attachments are not saved. 


## Tips

### Encryption: AutoTLS

PHPMailer checks on "AutoTLS" if the specified server supports TLS and establishes an encrypted TLS connection. If the server does not allow encryption, an insecure connection will be established. If there should be problems with the connection, it is often due to the fact that the deposited certificate does not agree with the indicated host or no valid certificate was found. By changing the encryption to "manual selection" the automatic recognition can be deactivated and the encryption can be selected manually. 

> This setting can lead to insecure connections if TLS support is not found. A debug mode check **connection protocol** should be performed.


### Spam-Blocker

- The server that sends the e-mails should be listed in the DNS as authorized server for the sending e-mail domain, defined via SPF record.

- The priority settings can lead to spam blocking.

- Large e-mail distribution lists should be sent in small numbers and as BCCs if possible.

> SMTP transmission does not allow sending emails with empty body 

### Use of self-signed certificates

The peer is verified by default. This can lead to problems. The following settings help to avoid this problem.

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

### Sending mails over different domains 

If e-mails are sent via different sender domains, the SPF record of the sender domain(s) should include proper settings for

- the web server (if you use sendmail or mail) 
- or the specified SMTP(S) server 

e.g. `a:my-domain.tld ip4:XXX.XXX.XXX.XXX`

This ensures that PHPMailer can send emails under the specified domain and that the mail is not declared as SPAM.  

If necessary, contact the registrar or DNS administrator. 

