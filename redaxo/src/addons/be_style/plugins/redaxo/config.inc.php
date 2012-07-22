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

  rex_extension::register('PAGE_HEADER', function ($params) use ($mypage) {
    $params['subject'] .= '
      <link href="' . rex_url::pluginAssets('be_style', $mypage, 'css_import.css') . '" rel="stylesheet" type="text/css" media="all" />';
    return $params['subject'];
  });
}
