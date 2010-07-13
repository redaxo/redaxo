<?php

/**
 * COM - Plugin - Messages
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

$sql = new rex_sql();
$sql->setQuery("DROP TABLE `rex_com_message`");
$sql->setQuery('SHOW TABLE STATUS LIKE "rex_com_message"');

if ($sql->getRows() == 1)
{
	$REX['ADDON']['install']['messages'] = 1;
	$REX['ADDON']['installmsg']['messages'] = 'Tabelle "rex_com_message" konnte nicht gelöscht werden';
}else
{
	$REX['ADDON']['install']['messages'] = 0;
}

?>