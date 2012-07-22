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
 * @package redaxo5
 */

$mypage = 'agk_skin';

if (rex::isBackend()) {

  rex_extension::register('PAGE_HEADER', function ($params) use ($mypage) {
    $params['subject'] .= '
    <link rel="stylesheet" href="' . rex_url::pluginAssets('be_style', $mypage, 'css_import.css') . '" type="text/css" media="screen, projection, print" />
    <!--[if lte IE 7]>
        <link rel="stylesheet" href="' . rex_url::pluginAssets('be_style', $mypage, 'css_ie_lte_7.css') . '" type="text/css" media="screen, projection, print" />
        <link rel="stylesheet" href="' . rex_url::pluginAssets('be_style', $mypage, 'css_agk_ie_lte_7.css') . '" type="text/css" media="screen, projection, print" />
      <![endif]-->
      <!--[if lte IE 6]>
        <link rel="stylesheet" href="' . rex_url::pluginAssets('be_style', $mypage, 'css_ie_lte_6.css') . '" type="text/css" media="screen, projection, print" />
        <link rel="stylesheet" href="' . rex_url::pluginAssets('be_style', $mypage, 'css_agk_ie_lte_6.css') . '" type="text/css" media="screen, projection, print" />
      <![endif]-->';
    return $params['subject'];
  });

  rex_extension::register('PAGE_BODY_ATTR', function ($params) {
    $params['subject']['class'][] = 'be-style-agk-skin';
    return $params['subject'];
  });
}
