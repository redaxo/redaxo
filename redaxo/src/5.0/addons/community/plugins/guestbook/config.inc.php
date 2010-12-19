<?php

if ($REX["REDAXO"])
{
	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['subpages'][] = array('plugin.guestbook','Gästebuch');

	// Im Setup aufnehmen - für Module.
	$REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("guestbook","guestbook","1201 - COM-Module - Gästebuch");

	// EMails
	$REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("guestbook","sendemail_guestbook","sendemail_guestbook","Community: Neuer Eintrag in Ihr Gästebuch", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);

}else
{




}

?>