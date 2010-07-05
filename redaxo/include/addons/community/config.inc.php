<?php

$mypage = "community"; // only for this file

// ********** Allgemeine AddOn Config
$REX['ADDON']['rxid'][$mypage] = '5';

$REX['ADDON']['name'][$mypage] = "Community";   // name
$REX['ADDON']['perm'][$mypage] = "community[]"; // benoetigt mindest permission
$REX['ADDON']['navigation'][$mypage] = array('block'=>'community');

$REX['ADDON']['version'][$mypage] = '1.5';
$REX['ADDON']['author'][$mypage] = 'Jan Kristinus';
$REX['ADDON']['supportpage'][$mypage] = 'redaxo.yakamara.de';
$REX['PERM'][] = "community[]";

if (isset($I18N) && is_object($I18N))
  $I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/lang');

// ********** Community User Funktionen
include $REX["INCLUDE_PATH"]."/addons/community/classes/class.rex_com.inc.php";

// ********** Backend, Perms, Subpages etc.
if ($REX["REDAXO"] && $REX['USER'])
{
	$REX['EXTRAPERM'][] = "community[admin]";
	$REX['EXTRAPERM'][] = "community[users]";
	include $REX["INCLUDE_PATH"]."/addons/community/functions/functions.userconfig.inc.php";
	
	$REX['ADDON'][$mypage]['SUBPAGES'] = array();
	$REX['ADDON'][$mypage]['SUBPAGES'][] = array( '' , '&Uuml;bersicht');
	
	// Feste Subpages
	if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm("community[users]")) 
		$REX['ADDON'][$mypage]['SUBPAGES'][] = array ('user' , 'User Verwaltung');
	if ($REX['USER']->isAdmin() || $REX['USER']->hasPerm("community[admin]")) 
		$REX['ADDON'][$mypage]['SUBPAGES'][] = array ('user_fields' , 'User Felder erweitern');
	
	if($REX["REDAXO"])
	{

	}
}


// allgemeine feldtypen

$REX["ADDON"]["community"]["ut"] = array();
$REX["ADDON"]["community"]["ut"][1] = "INT(11)";
$REX["ADDON"]["community"]["ut"][2] = "VARCHAR(255)";
$REX["ADDON"]["community"]["ut"][3] = "TEXT";
$REX["ADDON"]["community"]["ut"][4] = "PASSWORD";
$REX["ADDON"]["community"]["ut"][5] = "SELECT";
$REX["ADDON"]["community"]["ut"][6] = "BOOL";
$REX["ADDON"]["community"]["ut"][7] = "FLOAT(10,7) für Positionen wie lat und lng";
$REX["ADDON"]["community"]["ut"][8] = "SQL SELECT";
$REX["ADDON"]["community"]["ut"][9] = "REDAXO MEDIALIST";
$REX["ADDON"]["community"]["ut"][10] = "REDAXO MEDIA";



// feste felder
$REX["ADDON"]["community"]["ff"] = array();
$REX["ADDON"]["community"]["ff"][] = "id";
$REX["ADDON"]["community"]["ff"][] = "login";
$REX["ADDON"]["community"]["ff"][] = "password";
$REX["ADDON"]["community"]["ff"][] = "email";
$REX["ADDON"]["community"]["ff"][] = "status";
$REX["ADDON"]["community"]["ff"][] = "name";
$REX["ADDON"]["community"]["ff"][] = "firstname";
$REX["ADDON"]["community"]["ff"][] = "activation_key";

/*
$ff[] = "session_id";
$ff[] = "last_xs";
$ff[] = "last_login";
$ff[] = "email_checked";
$ff[] = "activation_key";
$ff[] = "last_newsletterid";

$ff[] = "gender";
$ff[] = "street";
$ff[] = "zip";
$ff[] = "city";
$ff[] = "phone";
$ff[] = "birthday";

*/


// ********** XForm values/action/validations einbinden
// $REX['INCLUDE_PATH'].'/addons/community/xform/classes/value/'

$REX['ADDON']['community']['xform_path']['value'] = array();
$REX['ADDON']['community']['xform_path']['validate'] = array();
$REX['ADDON']['community']['xform_path']['action'] = array();

$REX['ADDON']['community']['xform_path']['value'][] = $REX["INCLUDE_PATH"]."/addons/community/xform/value/";

rex_register_extension('ADDONS_INCLUDED', 'rex_com_xform_add');
function rex_com_xform_add($params){
	global $REX;
	foreach($REX['ADDON']['community']['xform_path']['value'] as $value)
	{
		$REX['ADDON']['xform']['classpaths']['value'][] = $value;
	}
	foreach($REX['ADDON']['community']['xform_path']['validate'] as $validate)
	{
		$REX['ADDON']['xform']['classpaths']['validate'][] = $validate;
	}
	foreach($REX['ADDON']['community']['xform_path']['action'] as $action)
	{
		$REX['ADDON']['xform']['classpaths']['action'][] = $action;
	}

}