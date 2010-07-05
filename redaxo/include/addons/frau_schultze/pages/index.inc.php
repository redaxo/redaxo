<?php

/**
 * Url Rewrite Addon - Plugin url_marketing
 *
 * @author kai.kristinus[at]yakamara[dot]de - Kai Kristinus
 * @author <a href="http://www.yakamara.de/">yakamara</a>
 * 
 * @author mail[at]blumbeet[dot]com Thomas Blum
 * @author <a href="http://www.blumbeet.com/">blumbeet - web.studio</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */
 
$mypage = 'frau_schultze';
$basedir = dirname(__FILE__);
 
require_once ($REX['INCLUDE_PATH'] .'/layout/top.php');
require_once ($basedir.'/../settings.inc.php');

$page    = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string', '');
$func    = rex_request('func', 'string');
$oid     = rex_request('oid', 'int');

rex_title($I18N->msg('a724_addon_title'), $REX['ADDON']['pages'][$mypage]);

echo "\n  <div class=\"rex-addon-output-v2\">\n  ";

if ($subpage == '')
	$subpage = 'url_marketing';

require_once ($REX['INCLUDE_PATH'] .'/addons/'.$mypage.'/pages/'. $subpage .'.inc.php');

echo "\n  </div>";

require_once ($REX['INCLUDE_PATH'] .'/layout/bottom.php');