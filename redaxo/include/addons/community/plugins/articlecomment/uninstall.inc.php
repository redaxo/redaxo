<?php

/**
 * COM - Plugin - Board
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

$sql = new rex_sql();
$sql->setQuery("DROP TABLE `rex_com_comment`");
$sql->setQuery('SHOW TABLE STATUS LIKE "rex_com_comment"');

if ($sql->getRows() == 1)
{
	$REX['ADDON']['install']['articlecomment'] = 1;
	$REX['ADDON']['installmsg']['articlecomment'] = 'Tabelle "rex_com_comment" konnte nicht gelöscht werden';
}else
{
	$REX['ADDON']['install']['articlecomment'] = 0;
}

?>