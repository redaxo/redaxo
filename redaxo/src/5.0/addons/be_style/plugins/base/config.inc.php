<?php

/**
 * REDAXO Basis-Theme
 *
 * @author Umsetzung
 * @author thomas[dot]blum[at]redaxo[dot]de Thomas Blum
 * @author <a href="http://www.blumbeet.com">www.blumbeet.com</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'base';

$REX['ADDON']['version'][$mypage] = '1.0';
$REX['ADDON']['author'][$mypage] = 'Thomas Blum';
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum/';

if($REX["REDAXO"])
{

	function rex_be_style_base_css_add($params)
  {

  	$params["subject"] = '
  <link rel="stylesheet" type="text/css" href="'. rex_path::pluginAssets('be_style', 'base', 'css_import.css', true) .'" media="screen, projection, print" />

  <!--[if lte IE 7]>
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', 'base', 'css_ie_lte_7.css', true) .'" type="text/css" media="screen, projection, print" />
  <![endif]-->

  <!--[if IE 7]>
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', 'base', 'css_ie_7.css', true) .'" type="text/css" media="screen, projection, print" />
  <![endif]-->

  <!--[if lte IE 6]>
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', 'base', 'css_ie_lte_6.css', true) .'" type="text/css" media="screen, projection, print" />
  <![endif]-->

  '.$params["subject"];




    return $params["subject"];
  }

	rex_register_extension('PAGE_HEADER', "rex_be_style_base_css_add");


	function rex_be_style_base_css_body($params)
  {
    $params["subject"]["class"][] = "be-style-base";
    return $params["subject"];
  }

  rex_register_extension('PAGE_BODY_ATTR', 'rex_be_style_base_css_body');


}