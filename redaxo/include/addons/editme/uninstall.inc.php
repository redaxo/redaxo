<?php

/**
 * editme
 *
 * @author jan@kristinus.de
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

$pre = $REX['TABLE_PREFIX'].'em_data_';
$prelen = strlen($pre);

$sql = rex_sql::factory();
$sql->setQuery("show tables;");
$tables = $sql->getArray();

foreach($tables as $table)
{
	$tablename = current($table);
	if(substr($tablename,0,$prelen)==$pre)
	{
		$sql->setQuery('DROP TABLE IF EXISTS `'. $tablename .'`;');
		if($sql->hasError())
		{
			$error .= 'MySQL Error '. $sql->getErrno() .': '. $sql->getError();
			break;
		}
	}
}

if($error == '')
{
	$REX['ADDON']['install']['editme'] = 0;
}
else
{
	$REX['ADDON']['installmsg']['editme'] = $error;
}
