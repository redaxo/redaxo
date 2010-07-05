<?php

/**
 * Url Marketing Addon - "Frau Schultze"
 *
 * @author kai.kristinus[at]yakamara[dot]de - Kai Kristinus
 * @author <a href="http://www.yakamara.de/">yakamara</a>
 * 
 * @author mail[at]blumbeet[dot]com Thomas Blum
 * @author <a href="http://www.blumbeet.com/">blumbeet - web.studio</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'frau_schultze';

if($REX['REDAXO'])
{
  // Sprachdateien anhaengen
  $I18N->appendFile(dirname(__FILE__) .'/lang/');

	/* Addon Parameter */
	$REX['ADDON']['rxid'][$mypage] = '724';
	$REX['ADDON']['name'][$mypage] = $I18N->msg('a724_addon_name');
	$REX['ADDON']['perm'][$mypage] = $mypage.'[]';
	$REX['ADDON']['version'][$mypage] = '0.0';
	$REX['ADDON']['author'][$mypage] = 'Kai Kristinus, Thomas Blum';
	$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';
	$REX['PERM'][] = $mypage.'[]';

}


if($REX['REDAXO'])
{
  
  // handle backend pages
  $I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');
  
  $page1 = new rex_be_page($I18N->msg('a724_subpage_marketing'), array(
      'page' => $mypage,
      'subpage' => ''
    )
  ); 
  $page1->setHref('index.php?page='.$mypage);
  
  
  $page2 = new rex_be_page($I18N->msg('a724_subpage_url_table'), array(
      'page' => $mypage,
      'subpage' => 'url_table'
    )
  );
  $page2->setHref('index.php?page='.$mypage.'&subpage=url_table');
  
  
  $page3 = new rex_be_page($I18N->msg('a724_subpage_description'), array(
      'page' => $mypage,
      'subpage' => 'description'
    )
  );
  $page3->setHref('index.php?page='.$mypage.'&subpage=description');
  
	$REX['ADDON']['pages'][$mypage] = array (
	  $page1, $page2, $page3
	);

}

require_once dirname(__FILE__).'/extensions/extension_common.inc.php';
rex_register_extension('URL_REWRITE_ARTICLE_ID_NOT_FOUND', 'a724_frau_schultze');

require_once dirname(__FILE__).'/functions/function_url_table.inc.php';
