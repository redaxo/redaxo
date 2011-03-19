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

$mypage = 'base_old';

$REX['ADDON']['version'][$mypage] = '1.0';
$REX['ADDON']['author'][$mypage] = 'Design: Ralph Zumkeller; Umsetzung: Thomas Blum';
$REX['ADDON']['supportpage'][$mypage] = 'www.redaxo.org/de/forum/';

if($REX["REDAXO"])
{

	function rex_be_style_base_old_css_add($params)
  {
  	$params["subject"] = '
  <link rel="stylesheet" type="text/css" href="'. rex_path::pluginAssets('be_style', 'base_old', 'css_import.css', rex_path::RELATIVE) .'" media="screen, projection, print" />
  <!--[if lte IE 7]>
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', 'base_old', 'css_ie_lte_7.css', rex_path::RELATIVE) .'" type="text/css" media="screen, projection, print" />
  <![endif]-->

  <!--[if IE 7]>
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', 'base_old', 'css_ie_7.css', rex_path::RELATIVE) .'" type="text/css" media="screen, projection, print" />
  <![endif]-->

  <!--[if lte IE 6]>
    <link rel="stylesheet" href="'. rex_path::pluginAssets('be_style', 'base_old', 'css_ie_lte_6.css', rex_path::RELATIVE) .'" type="text/css" media="screen, projection, print" />
  <![endif]-->

  <!-- jQuery immer nach den Stylesheets! -->
  <script src="'. rex_path::pluginAssets('be_style', 'base_old', 'jquery.min.js', rex_path::RELATIVE) .'" type="text/javascript"></script>
  <script src="'. rex_path::pluginAssets('be_style', 'base_old', 'standard.js', rex_path::RELATIVE) .'" type="text/javascript"></script>
  <script type="text/javascript">
  <!--
  var redaxo = true;

  // jQuery is now removed from the $ namespace
  // to use the $ shorthand, use (function($){ ... })(jQuery);
  // and for the onload handler: jQuery(function($){ ... });
  jQuery.noConflict();

  //-->
  </script>
  '.$params["subject"];




    return $params["subject"];
  }
	rex_register_extension('PAGE_HEADER', "rex_be_style_base_old_css_add");


	function rex_be_style_base_old_css_body($params)
  {
    $params["subject"]["class"][] = "be-style-base-old";
    return $params["subject"];
  }

  rex_register_extension('PAGE_BODY_ATTR', 'rex_be_style_base_old_css_body');


}