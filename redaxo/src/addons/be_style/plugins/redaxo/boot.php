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

    $files = [
        $this->getAddOn()->getPath('vendor/bootstrap/assets/javascripts/bootstrap.js') => $this->getAssetsPath('javascripts/bootstrap.js'),
        $this->getAddOn()->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.eot') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.eot'),
        $this->getAddOn()->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.svg') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.svg'),
        $this->getAddOn()->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.ttf') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.ttf'),
        $this->getAddOn()->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.woff') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.woff'),
        $this->getAddOn()->getPath('vendor/font-awesome/fonts/fontawesome-webfont.eot') => $this->getAssetsPath('fonts/fontawesome-webfont.eot'),
        $this->getAddOn()->getPath('vendor/font-awesome/fonts/fontawesome-webfont.svg') => $this->getAssetsPath('fonts/fontawesome-webfont.svg'),
        $this->getAddOn()->getPath('vendor/font-awesome/fonts/fontawesome-webfont.ttf') => $this->getAssetsPath('fonts/fontawesome-webfont.ttf'),
        $this->getAddOn()->getPath('vendor/font-awesome/fonts/fontawesome-webfont.woff') => $this->getAssetsPath('fonts/fontawesome-webfont.woff'),
        $this->getAddOn()->getPath('vendor/font-awesome/fonts/FontAwesome.otf') => $this->getAssetsPath('fonts/FontAwesome.otf'),
    ];


    foreach ($files as $source => $destination) {

        if (! file_exists($destination)) {

            rex_file::copy($source, $destination);

        }

    }

    if ($this->getProperty('compile')) {

        $compiler = new rex_scss_compiler();
        $compiler->setScssFile($this->getPath('scss/master.scss'));

        // Compile in frontend assets dir
        $compiler->setCssFile($this->getAssetsPath('css/styles.css'));

        // Compile in backend assets dir
        $compiler->setCssFile($this->getPath('assets/styles.css'));

        $compiler->compile();

    }

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/bootstrap.js'));

}
