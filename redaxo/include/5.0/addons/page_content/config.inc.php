<?php

/**
 * Page Content Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'page_content';

// $REX['ADDON']['rxid'][$mypage] = '62';
$REX['ADDON']['name'][$mypage] = $I18N->msg('content');
$REX['ADDON']['perm'][$mypage] = 'hasStructurePerm';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'system');

if ($REX['REDAXO'])
{
  $I18N->appendFile($REX['SRC_PATH'] . '/addons/' . $mypage . '/lang');
}