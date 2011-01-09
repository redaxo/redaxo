<?php

if ($REX["REDAXO"])
{

	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['subpages'][] = array('plugin.articlecomment','Artikelkommentare');
	$REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("articlecomment","articlecomment","1501 - COM-Module - Artikelkommentar");

}


?>