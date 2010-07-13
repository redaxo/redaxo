<?php

/**
 * Textile Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'textile';

$REX['ADDON']['rxid'][$mypage] = '79';
$REX['ADDON']['name'][$mypage] = 'Textile';
$REX['ADDON']['perm'][$mypage] = 'textile[]';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab, Dean Allen www.textism.com";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

$REX['PERM'][] = 'textile[]';
$REX['EXTPERM'][] = 'textile[help]';

require_once($REX['INCLUDE_PATH']. '/addons/textile/classes/class.textile.inc.php');
require_once $REX['INCLUDE_PATH']. '/addons/textile/functions/function_textile.inc.php';

if ($REX['REDAXO'])
{
  require_once $REX['INCLUDE_PATH'].'/addons/textile/extensions/function_extensions.inc.php';
  require_once $REX['INCLUDE_PATH'].'/addons/textile/functions/function_help.inc.php';

  $I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');
  
  rex_register_extension('PAGE_HEADER', 'rex_a79_css_add');
}
