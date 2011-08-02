<?php

/**
 * Page Content Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'content';

if (rex::isBackend())
{
  $page = new rex_be_page(rex_i18n::msg('content'), array('page' => 'linkmap'));
  $page->setRequiredPermissions('hasStructurePerm');
  $page->setHidden(true);
  $this->setProperty('page', new rex_be_page_main('system', $page));
}

rex_var::registerVar('rex_var_value');

rex_extension::register('CLANG_DELETED',
  function($params)
  {
    $del = rex_sql::factory();
    $del->setQuery("delete from ". rex::getTablePrefix() ."article_slice where clang='". $params['id'] ."'");
  }
);