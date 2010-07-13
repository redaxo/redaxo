<?php

// Kommentare mit Kommentarkey
// - slice1231
// - article1231
// - partner1231
// - news123
// - date23456

// TODO: Installatin eines Moduls
// TODO: Erklaerung der function rex_com_comment
// TODO: iframe it Kommentare und Bewertungsfunktion

include $REX['INCLUDE_PATH'].'/addons/community/plugins/comments/functions/function.rex_com_comment.inc.php';

if ($REX["REDAXO"])
{
	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['SUBPAGES'][] = array('plugin.comments','Kommentare');

	// Im Setup aufnehmen - für Module.
	// $REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("guestbook","guestbook","1201 - COM-Module - Gästebuch");

	// EMails
	// $REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("guestbook","sendemail_guestbook","sendemail_guestbook","Community: Neuer Eintrag in Ihr Gästebuch", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);

	// $REX['ADDON']['community']['xform_path']['validate'] = array($REX['INCLUDE_PATH'].'/addons/community/plugins/comments/xform/classes/validate/');
	
}