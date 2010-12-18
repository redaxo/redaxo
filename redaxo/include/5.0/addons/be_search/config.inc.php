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

// Suchmodus
// global => Es werden immer alle Kategorien durchsucht
// local => Es werden immer die aktuelle+Unterkategorien durchsucht
// $REX['ADDON']['searchmode'][$mypage] = 'global';
$REX['ADDON']['searchmode'][$mypage] = 'local';

$REX['EXTPERM'][] = 'be_search[mediapool]';
$REX['EXTPERM'][] = 'be_search[structure]';

if ($REX['REDAXO'])
{
  // Include Functions
  require_once $REX['SRC_PATH'] .'/addons/be_search/functions/functions.search.inc.php';
  
  rex_register_extension('PAGE_CHECKED', 'rex_a256_extensions_handler');
}
