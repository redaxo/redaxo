<?php

include $REX['INCLUDE_PATH'].'/addons/community/plugins/twitter/classes/class.twitter.inc.php';
include $REX['INCLUDE_PATH'].'/addons/community/plugins/twitter/classes/class.rex_com_twitter.inc.php';

if ($REX["REDAXO"])
{
  // Diese Seite noch extra einbinden
  $REX['ADDON']['community']['SUBPAGES'][] = array('plugin.twitter','Twitter');
  
  // Im Setup aufnehmen - für Module.
  $REX["ADDON"]["community"]["plugins"]["setup"]["modules"][] = array("twitterline","twitterline","1301 - COM-Module - Twitterline");
  // EMails
  // $REX["ADDON"]["community"]["plugins"]["setup"]["emails"][] = array("guestbook","sendemail_guestbook","sendemail_guestbook","Community: Neuer Eintrag in Ihr Gästebuch", $REX['ERROR_EMAIL'], $REX['ERROR_EMAIL']);
  // $REX['ADDON']['community']['xform_path']['validate'] = array($REX['INCLUDE_PATH'].'/addons/community/plugins/comments/xform/classes/validate/');
  
}

?>