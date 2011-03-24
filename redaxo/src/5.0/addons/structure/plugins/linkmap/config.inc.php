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

  if($REX["USER"])
  {
    rex_register_extension('PAGE_HEADER', function($params){
      $params['subject'] .= "\n  ".
        '<script type="text/javascript" src="'. rex_path::pluginAssets('structure', 'linkmap', 'linkmap.js', rex_path::RELATIVE) .'"></script>';

      return $params['subject'];
    });
  }
}

rex_var::registerVar('rex_var_link');