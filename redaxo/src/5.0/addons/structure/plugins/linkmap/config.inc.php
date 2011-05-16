<?php

/**
 * Site Structure Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'linkmap';

if (rex::isBackend())
{
  $page = new rex_be_page_popup(rex_i18n::msg('linkmap'), '', array('page' => 'linkmap'));
  $page->setHidden(true);
  $page->setRequiredPermissions('hasStructurePerm');

  $this->setProperty('page', new rex_be_page_main('system', $page));

  if(rex::getUser())
  {
    rex_extension::register('PAGE_HEADER', function($params){
      $params['subject'] .= "\n  ".
        '<script type="text/javascript" src="'. rex_path::pluginAssets('structure', 'linkmap', 'linkmap.js', rex_path::RELATIVE) .'"></script>';

      return $params['subject'];
    });
  }
}

rex_var::registerVar('rex_var_link');