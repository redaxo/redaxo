<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'structure';

// $REX['ADDON']['rxid'][$mypage] = '62';
$REX['ADDON']['name'][$mypage] = $I18N->msg('structure');
$REX['ADDON']['perm'][$mypage] = 'hasStructurePerm';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab";
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
$REX['ADDON']['navigation'][$mypage] = array('block'=>'system');

if ($REX['REDAXO'])
{
  $I18N->appendFile(dirname(__FILE__) .'/lang');
}

$REX['VARIABLES'][] = 'rex_var_globals';
$REX['VARIABLES'][] = 'rex_var_article';
$REX['VARIABLES'][] = 'rex_var_category';