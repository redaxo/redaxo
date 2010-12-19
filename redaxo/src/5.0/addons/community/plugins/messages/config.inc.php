<?php

if ($REX["REDAXO"])
{
	// Diese Seite noch extra einbinden
	$REX['ADDON']['community']['subpages'][] = array('plugin.messages','Nachrichten');

	// Im Setup aufnehmen - für Module.
	$REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("messages","messages","1301 - COM-Module - Nachrichten");

	// EMails
	$REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("messages","sendemail_newmessage","sendemail_newmessage","Community: Neue private Nachricht", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);

	// IDs
	$REX["ADDON"]["community"]["plugins"]["setup"]["ids"][] = "REX_COM_PAGE_SENDMESSAGE_ID";

}else
{

	// Button bei Useransicht
	rex_register_extension('ADDON_COM_USER_BUTTONS_POST', 'rex_com_messages_add_buttons');
	function rex_com_messages_add_buttons($params)
	{
		global $REX;
		if ($REX['COM_USER']->getValue("rex_com_user.id") != $params["user_id"])
		{
			$params["buttons"][] = '<a href="'.rex_getUrl(REX_COM_PAGE_SENDMESSAGE_ID,0,array("user_id"=>$params["user_id"], "tab"=>2)).'"><span>Private Nachricht senden</span></a>';
		}
	}

}

?>