<?php

/**
 * XO-Form 
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

// email templates tabelle loeschen

$sql = rex_sql::factory();
$sql->setQuery('DROP TABLE `'.$REX['TABLE_PREFIX'].'xform_email_template`;');

$REX['ADDON']['install']['xform'] = 0;

?>