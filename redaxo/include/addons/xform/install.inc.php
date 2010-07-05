<?php

/**
 * XO-Form 
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */


// Tabelle anlegen Redaxo 4.0.x

$sql = rex_sql::factory();
$sql->setQuery('CREATE TABLE IF NOT EXISTS `'.$REX['TABLE_PREFIX'].'xform_email_template` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default "",
  `mail_from` varchar(255) NOT NULL default "",
  `mail_from_name` varchar(255) NOT NULL default "",
  `subject` varchar(255) NOT NULL default "",
  `body` text NOT NULL,
  PRIMARY KEY  (`id`)
);');

// evtl. Fehler beim Anlegen?
if ($sql->hasError())
{
	$msg = 'MySQL-Error: '.$sql->getErrno().'<br />';
	$msg .= $sql->getError();

	// Evtl Ausgabe einer Meldung
	$REX['ADDON']['install']['xform'] = 0;
	$REX['ADDON']['installmsg']['xform'] = $msg;
	
}else
{
	// Installation erfolgreich
		$REX['ADDON']['install']['xform'] = 1;
}

?>