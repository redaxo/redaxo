<?php

if ($REX["REDAXO"]
	&& 
		$REX['USER'] 
		&& 
		($REX['USER']->isAdmin() || $REX['USER']->hasPerm("community[admin]") || $REX['USER']->hasPerm("community[setup]"))
)
{

	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['SUBPAGES'][] = array('plugin.setup','Setup');
	
	// Module für das Setup aufnehmen
	// $REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("setup","tabbox","1001 - COM-Module - Tabbox");
	// $REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("setup","usersearch","1002 - COM-Module - Usersuche");
	// $REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("setup","userdetails","1003 - COM-Module - Userdetails");

	// Templates für das Setup aufnehmen
	$REX["ADDON"]["community"]["plugins"]["setup"]["templates"][] = array("setup","auth","1012 - COM-Template - Basis - Authentifizierung",0);
	$REX["ADDON"]["community"]["plugins"]["setup"]["templates"][] = array("setup","userlogin","1014 - COM-Template - Basis - Userloginfenster",0);
	$REX["ADDON"]["community"]["plugins"]["setup"]["templates"][] = array("setup","navi","1015 - COM-Template - Basis - Navigation mit 3 Ebenen",0);
	// $REX["ADDON"]["community"]["plugins"]["setup"]["templates"][] = array("setup","navi_user","1016 - COM-Template - Basis - Navigation - Userbereiche 1 Ebene",0);

	// E-Mail Templates für das Setup aufnehmen
	$REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("setup","register","register","Community: Bitte bestätigen Sie die Registrierung", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);
	$REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("setup","send_password","send_password","Community: Neues Passwort", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);

	// Ids
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_PROFIL_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_MYPROFIL_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_REGISTER_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_REGISTER_ACCEPT_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_PSWFORGOTTEN_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_LOGIN_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_PAGE_LOGOUT_ID";
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "COM_USERCAT_ID";

}

// Allgemeine Config

// ----------------- DONT EDIT BELOW THIS
// --- DYN
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_PROFIL_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_MYPROFIL_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_REGISTER_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_REGISTER_ACCEPT_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_PSWFORGOTTEN_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_LOGIN_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_PAGE_LOGOUT_ID"] = "1";
$REX["ADDON"]["COMMUNITY_VARS"]["COM_USERCAT_ID"] = "1";
// --- /DYN
// ----------------- /DONT EDIT BELOW THIS

?>