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

  rex_be_controller::addCssFile(rex_url::backendController(array('be_style_' . $mypage . '_font' => 'entypo')));
  rex_be_controller::addCssFile($this->getAssetsUrl('import.css'));
  rex_be_controller::addJsFile($this->getAssetsUrl('js.js'));

  rex_extension::register('PAGE_BODY_ATTR', function ($params) {
    $params['subject']['class'][] = 'redaxo';
    return $params['subject'];
  });
}
