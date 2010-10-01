<?php

/**
 * Page Content Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'content';

if ($REX['REDAXO'])
{
  // $REX['ADDON']['rxid'][$mypage] = '62';
  $page = new rex_be_page($I18N->msg('content'), array('page' => 'linkmap'));
  $page->setRequiredPermissions('hasStructurePerm');
  $page->setHidden(true);
  $REX['ADDON']['page'][$mypage] = new rex_be_page_main('system', $page);
  
  $REX['ADDON']['version'][$mypage] = "1.3";
  $REX['ADDON']['author'][$mypage] = "Markus Staab";
  $REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
}

$REX['VARIABLES'][] = 'rex_var_value';