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

/** @var rex_addon $myAddon */
$myAddon = $this;

$mypage = 'be_style';

/* Addon Parameter */
if (rex::isBackend()) {
    rex_extension::register('PACKAGES_INCLUDED', function () {
        if (rex_extension::isRegistered('BE_STYLE_PAGE_CONTENT')) {
            rex_addon::get('be_style')->setProperty('name', 'Backend Style');
        }
    });

    rex_extension::register('BE_STYLE_SCSS_COMPILE', function (rex_extension_point $ep) use ($myAddon) {
        $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', []));

        $subject = $ep->getSubject();
        $subject[] = [
            'scss_files' => array_merge($scss_files, [$myAddon->getPath('scss/master.scss')]),
            'css_file' => $myAddon->getPath('assets/css/styles.css'),
            'copy_dest' => $myAddon->getAssetsPath('css/styles.css'),
        ];
        $subject[] = [
            'scss_files' => array_merge($scss_files, [$myAddon->getPath('scss/master_minibar.scss')]),
            'css_file' => $myAddon->getPath('assets/css/minibar.css'),
            'copy_dest' => $myAddon->getAssetsPath('css/minibar.css'),
        ];
        return $subject;
    });

    rex_extension::register('PACKAGES_INCLUDED', function () use ($myAddon) {
        if (rex::getUser() && $myAddon->getProperty('compile')) {
            rex_be_style::compile();
        }
    });

    rex_view::addCssFile($myAddon->getAssetsUrl('css/styles.css'));
    if (rex_minibar::getInstance()->isActive()) {
        rex_view::addCssFile($myAddon->getAssetsUrl('css/minibar.css'));
    }
    rex_view::addCssFile($myAddon->getAssetsUrl('css/bootstrap-select.min.css'));
    rex_view::addCssFile($myAddon->getAssetsUrl('css/perfect-scrollbar.min.css'));
    rex_view::addJsFile($myAddon->getAssetsUrl('javascripts/bootstrap.js'));
    rex_view::addJsFile($myAddon->getAssetsUrl('javascripts/bootstrap-select.min.js'));
    rex_view::addJsFile($myAddon->getAssetsUrl('javascripts/bootstrap-select-defaults-de_DE.min.js'));
    rex_view::addJsFile($myAddon->getAssetsUrl('javascripts/perfect-scrollbar.jquery.min.js'));
    rex_view::addJsFile($myAddon->getAssetsUrl('javascripts/main.js'));

    // make sure to send preload headers only on fullpage requests
    if (stripos(rex_request::server('HTTP_ACCEPT'), 'text/html') !== false && !rex_request::isXmlHttpRequest()) {
        rex_response::preload($myAddon->getAssetsUrl('fonts/fontawesome-webfont.woff2?v=4.7.0'), 'font', 'font/woff2');
    }
}
