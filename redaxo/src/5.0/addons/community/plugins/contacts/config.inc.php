<?php



if ($REX["REDAXO"])
{

	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['subpages'][] = array('plugin.contacts','Kontakte');

	// Module für das Setup aufnehmen
	$REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("contacts","contacts","1101 - COM-Module - Kontaktbox");

	// Emails aufnehmen
	$REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("contacts","sendemail_contactrequest","sendemail_contactrequest","Community: Neue Kontaktanfrage", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);

}


?>