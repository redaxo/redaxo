<?php

/**
 * Layouting Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'templates';

// $REX['ADDON']['rxid'][$mypage] = '62';
$REX['ADDON']['name'][$mypage] = 'Templates';
 $REX['ADDON']['perm'][$mypage] = 'admin[]';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'system');

if ($REX['REDAXO'])
{
  $I18N->appendFile($REX['SRC_PATH'] . '/addons/' . $mypage . '/lang');
  rex_autoload::getInstance()->addDirectory(dirname(__FILE__) .'/lib/');
}