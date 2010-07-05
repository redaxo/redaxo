<?php

/**
 * COM - Plugin - Board
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

$sql = new rex_sql();
$sql->setQuery("DROP TABLE `rex_com_board`");
$sql->setQuery('SHOW TABLE STATUS LIKE "rex_com_board"');

if ($sql->getRows() == 1)
{
	$REX['ADDON']['install']['board'] = true;
	$REX['ADDON']['installmsg']['board'] = 'Tabelle "rex_com_board" konnte nicht gelöscht werden';
}else
{
	$REX['ADDON']['install']['board'] = false;
}

?>