<?php

/**
 * COM - Plugin - guestbook
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

$sql = new rex_sql();
$sql->setQuery("DROP TABLE `rex_com_guestbook`");
$sql->setQuery('SHOW TABLE STATUS LIKE "rex_com_guestbook"');

if ($sql->getRows() == 1)
{
	$REX['ADDON']['install']['guestbook'] = 1;
	$REX['ADDON']['installmsg']['guestbook'] = 'Tabelle "rex_com_guestbook" konnte nicht gelöscht werden';
}else
{
	$REX['ADDON']['install']['guestbook'] = 0;
}

?>