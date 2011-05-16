<?php

/**
 * Backend Search Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'be_search';

// Suchmodus
// global => Es werden immer alle Kategorien durchsucht
// local => Es werden immer die aktuelle+Unterkategorien durchsucht
// $REX['ADDON']['searchmode'][$mypage] = 'global';
$this->setProperty('searchmode', 'local');

$REX['EXTPERM'][] = 'be_search[mediapool]';
$REX['EXTPERM'][] = 'be_search[structure]';

if (rex::isBackend())
{
  // Include Functions
  require_once rex_path::addon('be_search', 'functions/functions.search.inc.php');

  rex_extension::register('PAGE_CHECKED', 'rex_be_search_extensions_handler');
}
