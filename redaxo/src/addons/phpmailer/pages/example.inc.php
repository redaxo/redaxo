<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo5
 */

$mdl_ex = <<<EOD
<?php

\$mail = new rex_mailer();
\$sql = rex_sql::factory();

\$query = "SELECT full_name, email, photo FROM employee WHERE id= ?";
\$sql->setQuery(\$query, array(\$id));

foreach(\$sql as \$row)
{
    // HTML body
    \$body  = "Hello <font size=\"4\">" . \$row->getValue("full_name") . "</font>, <p>";
    \$body .= "<i>Your</i> personal photograph to this message.<p>";
    \$body .= "Sincerely, <br />";
    \$body .= "phpmailer List manager";

    // Plain text body (for mail clients that cannot read HTML)
    \$text_body  = "Hello " . \$row->getValue("full_name") . ", \\n\\n";
    \$text_body .= "Your personal photograph to this message.\\n\\n";
    \$text_body .= "Sincerely, \\n";
    \$text_body .= "phpmailer List manager";

    \$mail->Body    = \$body;
    \$mail->AltBody = \$text_body;
    \$mail->AddAddress(\$row->getValue("email"), \$row->getValue("full_name"));
    \$mail->AddStringAttachment(\$sql->getValue("photo"), "YourPhoto.jpg");

    if(!\$mail->Send())
        echo "There has been a mail error sending to " . \$row->getValue("email") . "<br>";

    // Clear all addresses and attachments for next loop
    \$mail->ClearAddresses();
    \$mail->ClearAttachments();
}

?>
EOD;


?>
<div class="rex-addon-output">
  <h2 class="rex-hl2"><?php echo $this->i18n('example_headline'); ?></h2>
  <div class="rex-addon-content">
    <?php echo rex_string::highlight($mdl_ex); ?>
  </div>
</div>
