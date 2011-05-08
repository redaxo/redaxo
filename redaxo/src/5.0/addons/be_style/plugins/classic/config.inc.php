<?php

/**
 * REDAXO Classic-Theme
 *
 * @author Umsetzung
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$mypage = 'classic';

$REX['ADDON']['version'][$mypage] = '1.3';
$REX['ADDON']['author'][$mypage] = 'Umsetzung: Thomas Blum';
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum/';

if($REX["REDAXO"])
{

	rex_extension::register('PAGE_HEADER', function ($params) use ($mypage)
	{
	  $params["subject"] .= '
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', $mypage, 'css_import.css', rex_path::RELATIVE) .'" type="text/css" media="screen, projection, print" />
	  <!--[if lte IE 7]>
	      <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', $mypage, 'css_ie_lte_7.css', rex_path::RELATIVE) .'" type="text/css" media="screen, projection, print" />
	    <![endif]-->
	    <!--[if lte IE 6]>
	      <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', $mypage, 'css_ie_lte_6.css', rex_path::RELATIVE) .'" type="text/css" media="screen, projection, print" />
	    <![endif]-->';
	  return $params["subject"];
	});


	rex_extension::register('PAGE_BODY_ATTR', function ($params)
	{
	  $params["subject"]["class"][] = "be-style-classic-skin";
	  return $params["subject"];
	});

}