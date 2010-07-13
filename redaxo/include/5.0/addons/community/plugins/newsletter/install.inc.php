<?php

/**
 * COM - Plugin - Newsletter
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

/*
	UserFelder hinzufŸgen
	- newsletter, bool
	- newsletter_last_id, varchar
  + Felder abgleichen
*/

$ins = new rex_sql;

$ins->setQuery("select * from `rex_com_user_field` where `userfield`='newsletter'");
if($ins->getRows()==0)
  $ins->setQuery("INSERT INTO `rex_com_user_field` SET `prior`='100',`name`='Newsletter schicken',`userfield`='newsletter',`type`='6',`extra1`='',`inlist`='',`editable`='1',`mandatory`='',`unique`='',`defaultvalue`='0'");

$ins->setQuery("select * from `rex_com_user_field` where `userfield`='newsletter_last_id'");
if($ins->getRows()==0)
  $ins->setQuery("INSERT INTO `rex_com_user_field` SET `prior`='110',`name`='Letzte Newsletter ID',`userfield`='newsletter_last_id',`type`='2',`extra1`='',`inlist`='',`editable`='1',`mandatory`='',`unique`='',`defaultvalue`=''");

rex_com_checkFields('rex_com_user_field', 'rex_com_user');

$error = '';

if ($error != '')
  $REX['ADDON']['installmsg']['newsletter'] = $error;
else
  $REX['ADDON']['install']['newsletter'] = true;

?>