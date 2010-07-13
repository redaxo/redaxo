<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'be_search';

/* Addon Parameter */
$REX['ADDON']['rxid'][$mypage] = '256';
//$REX['ADDON']['name'][$mypage] = 'Backend Search';
//$REX['ADDON']['perm'][$mypage] = 'be_search[]';
$REX['ADDON']['version'][$mypage] = '1.3';
$REX['ADDON']['author'][$mypage] = 'Markus Staab';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

// Suchmodus
// global => Es werden immer alle Kategorien durchsucht
// local => Es werden immer die aktuelle+Unterkategorien durchsucht
// $REX['ADDON']['searchmode'][$mypage] = 'global';
$REX['ADDON']['searchmode'][$mypage] = 'local';

$REX['EXTPERM'][] = 'be_search[mediapool]';
$REX['EXTPERM'][] = 'be_search[structure]';

if ($REX['REDAXO'])
{
  $I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');

  // Include Functions
  require_once $REX['INCLUDE_PATH'].'/addons/be_search/functions/functions.search.inc.php';
  
  rex_register_extension('PAGE_CHECKED', 'rex_a256_extensions_handler');
}
