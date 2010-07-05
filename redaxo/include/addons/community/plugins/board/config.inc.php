<?php


include $REX["INCLUDE_PATH"]."/addons/community/plugins/board/classes/class.rex_com_board.inc.php";


if ($REX["REDAXO"])
{

	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['subpages'][] = array('plugin.board','Boards');
	$REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("board","board","1401 - COM-Module - Board");
	$REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("board","boardteaser","1402 - COM-Module - Boardteaser");

}


?>