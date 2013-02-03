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

$mypage = 'redaxo';

if (rex::isBackend()) {

  require __DIR__ . '/pages/font.php';

  rex_extension::register('PAGE_HEADER', function ($params) use ($mypage) {
    $params['subject'] .= '
      <link href="' . rex_url::backend('index.php?be_style_' . $mypage . '_font=entypo') . '" rel="stylesheet" type="text/css" media="all" />
      <link href="' . rex_url::pluginAssets('be_style', $mypage, 'import.css') . '" rel="stylesheet" type="text/css" media="all" />
      <script src="' . rex_url::pluginAssets('be_style', $mypage, 'js.js') . '" type="text/javascript"></script>';
    return $params['subject'];
  });

  rex_extension::register('PAGE_BODY_ATTR', function ($params) {
    $params['subject']['class'][] = 'redaxo';
    return $params['subject'];
  });
}
