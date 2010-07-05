<?php

/**
 * COM - Plugin - Setup
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

$error = '';

if ($error != '')
  $REX['ADDON']['installmsg']['setup'] = $error;
else
  $REX['ADDON']['install']['setup'] = false;

?>