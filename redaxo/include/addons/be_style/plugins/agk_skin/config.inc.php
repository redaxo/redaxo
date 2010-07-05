<?php

/**
 * REDAXO Default-Theme
 * 
 * @author Design
 * @author ralph.zumkeller[at]yakamara[dot]de Ralph Zumkeller
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 * 
 * @author Umsetzung
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'agk_skin';

$REX['ADDON']['version'][$mypage] = '1.3';
$REX['ADDON']['author'][$mypage] = 'Design: Ralph Zumkeller; Umsetzung: Thomas Blum';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

if($REX["REDAXO"])
{
	
	function rex_be_style_agk_skin_css_add($params)
	{
	  $params["subject"] .= '      
	    <!--[if lte IE 7]>
	      <link rel="stylesheet" href="../files/addons/be_style/plugins/agk_skin/css_ie_lte_7.css" type="text/css" media="screen, projection, print" />
	    <![endif]-->
	    <!--[if lte IE 6]>
	      <link rel="stylesheet" href="../files/addons/be_style/plugins/agk_skin/css_ie_lte_6.css" type="text/css" media="screen, projection, print" />
	    <![endif]-->';
	  return $params["subject"];
	}

	rex_register_extension('PAGE_HEADER', 'rex_be_style_agk_skin_css_add');
  
	function rex_be_style_agk_skin_css_body($params)
	{
	  $params["subject"]["class"][] = "be-style-agb-skin";
	  return $params["subject"];
	}
  
	rex_register_extension('PAGE_BODY_ATTR', 'rex_be_style_agk_skin_css_body');

}