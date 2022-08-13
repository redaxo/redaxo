<?php

/**
 * Backendstyle Addon.
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="https://www.redaxo.org">www.redaxo.org</a>
 */

$addon = rex_addon::get('be_style');

/* Addon Parameter */
if (rex::isBackend()) {
    rex_extension::register('PACKAGES_INCLUDED', static function () use ($addon) {
        if (rex_extension::isRegistered('BE_STYLE_PAGE_CONTENT')) {
            $addon->setProperty('name', 'Backend Style');
        }
    });

    rex_extension::register('BE_STYLE_SCSS_COMPILE', static function (rex_extension_point $ep) use ($addon) {
        $scssFiles = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', []));

        $subject = $ep->getSubject();
        $subject[] = [
            'scss_files' => array_merge($scssFiles, [$addon->getPath('scss/master.scss')]),
            'css_file' => $addon->getPath('assets/css/styles.css'),
            'copy_dest' => $addon->getAssetsPath('css/styles.css'),
        ];
        return $subject;
    });

    rex_extension::register('PACKAGES_INCLUDED', static function () use ($addon) {
        if (rex::getUser() && $addon->getProperty('compile')) {
            rex_be_style::compile();
        }
    });

    rex_view::addCssFile($addon->getAssetsUrl('css/styles.css'));
    rex_view::addCssFile($addon->getAssetsUrl('css/bootstrap-select.min.css'));
    rex_view::addJsFile($addon->getAssetsUrl('javascripts/bootstrap.js'), [rex_view::JS_IMMUTABLE => true]);
    rex_view::addJsFile($addon->getAssetsUrl('javascripts/bootstrap-select.min.js'), [rex_view::JS_IMMUTABLE => true]);
    $bootstrapSelectLang = [
        'de_de' => 'de_DE',
        'en_gb' => 'en_US',
        'es_es' => 'de_DE',
        'it_it' => 'it_IT',
        'nl_nl' => 'nl_NL',
        'pt_br' => 'pt_BR',
        'sv_se' => 'sv_SE',
    ][rex_i18n::getLocale()] ?? 'en_US';
    rex_view::addJsFile($addon->getAssetsUrl('javascripts/bootstrap-select-defaults-'.$bootstrapSelectLang.'.min.js'), [rex_view::JS_IMMUTABLE => true]);
    rex_view::addJsFile($addon->getAssetsUrl('javascripts/main.js'), [rex_view::JS_IMMUTABLE => true]);
}
