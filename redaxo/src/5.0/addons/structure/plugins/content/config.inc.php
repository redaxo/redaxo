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

if (rex_core::isBackend())
{
  $page = new rex_be_page(rex_i18n::msg('content'), array('page' => 'linkmap'));
  $page->setRequiredPermissions('hasStructurePerm');
  $page->setHidden(true);
  $this->setProperty('page', new rex_be_page_main('system', $page));
}

rex_var::registerVar('rex_var_value');