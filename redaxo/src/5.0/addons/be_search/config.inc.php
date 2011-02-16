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
  require_once rex_path::addon('be_search', 'functions/functions.search.inc.php');

  rex_register_extension('PAGE_CHECKED', 'rex_be_search_extensions_handler');
}
