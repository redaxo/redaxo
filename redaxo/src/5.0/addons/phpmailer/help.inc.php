<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo4
 * @version svn:$Id$
 */

?>
<p>
PHPMailer Addon

<br /><br />

<a href="http://phpmailer.codeworxtech.com/">zur Seite des Erstellers</a>

<br /><br />

<?php
  $file = dirname( __FILE__) .'/_changelog.txt';
  if(is_readable($file))
    echo str_replace( '+', '&nbsp;&nbsp;+', nl2br(file_get_contents($file)));
?>
</p>