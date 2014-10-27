<?php

/**
 * REDAXO Default-Theme
 *
 * @author Design
 * @author ralph.zumkeller[at]yakamara[dot]de Ralph Zumkeller
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 *
 * @author Umsetzung
 * @author thomas[dot]blum[at]redaxo[dot]org Thomas Blum
 *
 * @package redaxo5
 *
 * @var rex_plugin $this
 */

$mypage = 'redaxo';

if (rex::isBackend()) {

    $compiler = new rex_scss_compiler();
    $compiler->setScssFile($this->getPath('scss/master.scss'));

    // Compile in frontend assets dir
    $compiler->setCssFile($this->getAssetsPath('styles.css'));

    // Compile in backend assets dir
    //$compiler->setCssFile($this->getPath('assets/styles.css'));

    $compiler->compile();
}
