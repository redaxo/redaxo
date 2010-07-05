<?php

$mypage = 'import_export';

if($REX['REDAXO'] && is_object($REX["USER"]))
{
	$I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');

	$REX['ADDON']['rxid'][$mypage] = '1';
	$REX['ADDON']['name'][$mypage] = $I18N->msg("im_export_importexport");
	$REX['ADDON']['perm'][$mypage] = 'import_export[export]';
	$REX['ADDON']['version'][$mypage] = "1.3";
	$REX['ADDON']['author'][$mypage] = "Jan Kristinus, Markus Staab";
	$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
	
	$REX['PERM'][] = 'import_export[export]';
	$REX['PERM'][] = 'import_export[import]';
	
	$REX['ADDON']['pages'][$mypage] = array();
	
 	if($REX["USER"]->hasPerm('import_export[import]') || $REX["USER"]->isAdmin())
 	{
		$REX['ADDON']['pages'][$mypage][] = array ('import', $I18N->msg('im_export_import'));
 	}
	$REX['ADDON']['pages'][$mypage][] = array ('', $I18N->msg('im_export_export'));
}

if(OOAddon::isAvailable('cronjob'))
{
  require_once dirname(__FILE__) .'/classes/class.cronjob.inc.php';
  
	rex_register_extension(
	  'CRONJOB_TYPES',
	  array('rex_cronjob_manager', 'registerExtension'),
	  array('class' => 'rex_cronjob_export')
  );
}