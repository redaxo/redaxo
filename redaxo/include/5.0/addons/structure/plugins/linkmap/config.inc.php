<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'linkmap';

if ($REX['REDAXO'])
{
  // $REX['ADDON']['rxid'][$mypage] = '62';
  $page = new rex_be_page_popup($REX['I18N']->msg('linkmap'), '', array('page' => 'linkmap'));
  $page->setHidden(true);
  $page->setRequiredPermissions('hasStructurePerm');

  $REX['ADDON']['page'][$mypage] = new rex_be_page_main('system', $page);
}

$REX['VARIABLES'][] = 'rex_var_link';