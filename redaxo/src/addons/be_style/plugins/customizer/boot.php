<?php

/**
 * REDAXO customizer.
 *
 * Codemirror by : http://codemirror.net/
 * Marijn Haverbeke <marijnh@gmail.com>
 */

if (rex::isUserLoggedIn()) {
    $config = $this->getConfig();

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));

    if ($config['codemirror']) {
        rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/codemirror.css'));
        rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/theme/'.$config['codemirror_theme'].'.css'));

        if ($config['codemirror_theme'] != '') {
            rex_view::setJsProperty('customizer_codemirror_defaulttheme', $config['codemirror_theme']);
        }

        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/codemirror-compressed.js'));
    }

    if ($config['labelcolor'] != '') {
        rex_view::setJsProperty('customizer_labelcolor', $config['labelcolor']);
    }

    if ($config['showlink']) {
        rex_view::setJsProperty('customizer_showlink', '<h1 class="be-style-customizer-title"><a href="'. rex::getServer() .'" target="_blank">' . rex::getServerName() . '</a><i class="fa fa-external-link"></i></h1>');
    }

    rex_view::addJsFile($this->getAssetsUrl('js/main.js'));
}
