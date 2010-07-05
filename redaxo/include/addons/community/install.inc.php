<?php

/**
 * Community Install 
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

if (OOAddon::isAvailable('xform') != 1 || OOAddon::isAvailable('phpmailer') != 1)
{

	// Installation nicht erfolgreich
	$REX['ADDON']['install']['community'] = 0;
	$REX['ADDON']['installmsg']['community'] = 'AddOn "XForm" und/oder "PHPMailer" ist nicht installiert und aktiviert.';

}elseif(OOAddon::getVersion('xform') < "1.4")
{
  $REX['ADDON']['install']['community'] = 0;
  $REX['ADDON']['installmsg']['community'] = 'Das AddOn "XForm" muss mindestens in der Version 1.4 vorhanden sein.';
		
}else
{

	// Metainfo erweitern
	$a = new rex_sql;
	$a->setTable("rex_62_params");
	$a->setValue("title","Zugriffsrechte");
	$a->setValue("prior","1");
	$a->setValue("type","3");
	$a->setValue("params","0:Alle|-1:Nur nicht Eingeloggte|1:Nur Eingeloggte|2:Nur Admins");
	$a->setValue("validate",NULL);
  $a->setValue("name","art_com_perm");
  
	$a->addGlobalCreateFields();

	$g = new rex_sql;
	$g->setQuery('select * from rex_62_params where name="art_com_perm"');
	
	if ($g->getRows()==1)
	{
		$a->setWhere('name="art_com_perm"');
		$a->update();
	}else
	{
		$a->insert();
	}
	
	$g = new rex_sql;
	$g->setQuery('show columns from rex_article Like "art_com_perm"');

	if ($g->getRows()==0) 
		$a->setQuery("ALTER TABLE `rex_article` ADD `art_com_perm` VARCHAR( 255 ) NOT NULL"); 
	
	$REX['ADDON']['install']['community'] = 1;

	// XForm vorhanden -> install.sql wird automatisch ausgeführt.

}

?>