<?php

/**
 * Backendstyle Addon.
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="https://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

$mypage = 'be_style';

/* Addon Parameter */
if (rex::isBackend()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex_extension::isRegistered('BE_STYLE_PAGE_CONTENT')) {
            rex_addon::get('be_style')->setProperty('name', 'Backend Style');
        }
    });

    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex::getUser() && $this->getProperty('compile')) {
            $compiler = new rex_scss_compiler();

            $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', [$this->getPath('scss/master.scss')]));
            $compiler->setScssFile($scss_files);
            //$compiler->setScssFile($this->getPath('scss/master.scss'));

            // Compile in backend assets dir
            $compiler->setCssFile($this->getPath('assets/css/styles.css'));

            $compiler->compile();

            // Compiled file to copy in frontend assets dir
            rex_file::copy($this->getPath('assets/css/styles.css'), $this->getAssetsPath('css/styles.css'));
        }
    });

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addCssFile($this->getAssetsUrl('css/bootstrap-select.min.css'));
    rex_view::addCssFile($this->getAssetsUrl('css/perfect-scrollbar.min.css'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/bootstrap.js'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/bootstrap-select.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/bootstrap-select-defaults-de_DE.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/perfect-scrollbar.jquery.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/main.js'));
    rex_response::preload($this->getAssetsUrl('fonts/fontawesome-webfont.woff2?v=4.7.0'), 'font', 'font/woff2');
}
