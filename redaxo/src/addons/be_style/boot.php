<?php

/**
 * Backendstyle Addon.
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="https://www.redaxo.org">www.redaxo.org</a>
 *
 *  REDAXO Default-Theme.
 *
 * @author Design
 * @author ralph.zumkeller[at]yakamara[dot]de Ralph Zumkeller
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
 * @author Umsetzung
 * @author thomas.blum[at]redaxo[dot]org Thomas Blum
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
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

        /** @var list<array{root_dir?: string, scss_files: string|list<string>, css_file: string, copy_dest?: string}> */
        $subject = $ep->getSubject();
        $subject[] = [
            'root_dir' => $addon->getPath('scss/'),
            'scss_files' => array_merge($scssFiles, [$addon->getPath('scss/master.scss')]),
            'css_file' => $addon->getPath('assets/css/styles.css'),
            'copy_dest' => $addon->getAssetsPath('css/styles.css'),
        ];
        $subject[] = [
            'root_dir' => $addon->getPath('scss/'),
            'scss_files' => $addon->getPath('scss/redaxo.scss'),
            'css_file' => $addon->getPath('assets/css/redaxo.css'),
            'copy_dest' => $addon->getAssetsPath('css/redaxo.css'),
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
    rex_view::addJsFile($addon->getAssetsUrl('js/bootstrap.js'), [rex_view::JS_IMMUTABLE => true]);
    rex_view::addJsFile($addon->getAssetsUrl('js/bootstrap-select.min.js'), [rex_view::JS_IMMUTABLE => true]);
    $bootstrapSelectLang = [
        'de_de' => 'de_DE',
        'en_gb' => 'en_US',
        'es_es' => 'de_DE',
        'it_it' => 'it_IT',
        'nl_nl' => 'nl_NL',
        'pt_br' => 'pt_BR',
        'sv_se' => 'sv_SE',
    ][rex_i18n::getLocale()] ?? 'en_US';
    rex_view::addJsFile($addon->getAssetsUrl('js/bootstrap-select-defaults-' . $bootstrapSelectLang . '.min.js'), [rex_view::JS_IMMUTABLE => true]);
    rex_view::addJsFile($addon->getAssetsUrl('js/main.js'), [rex_view::JS_IMMUTABLE => true]);
}

if (rex::isBackend()) {
    rex_view::addCssFile($addon->getAssetsUrl('css/redaxo.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/redaxo.js'), [rex_view::JS_IMMUTABLE => true]);

    rex_extension::register('PAGE_HEADER', static function (rex_extension_point $ep) use ($addon) {
        $themeColor = '#4d99d3';
        $config = $addon->getConfig();
        if (!empty($config['labelcolor'])) {
            $themeColor = $config['labelcolor'];
        }

        $icons = [];
        $icons[] = '<link rel="apple-touch-icon" sizes="180x180" href="' . $addon->getAssetsUrl('icons/apple-touch-icon.png') . '">';
        $icons[] = '<link rel="icon" type="image/png" sizes="32x32" href="' . $addon->getAssetsUrl('icons/favicon-32x32.png') . '">';
        $icons[] = '<link rel="icon" type="image/png" sizes="16x16" href="' . $addon->getAssetsUrl('icons/favicon-16x16.png') . '">';
        $icons[] = '<link rel="manifest" href="' . $addon->getAssetsUrl('icons/site.webmanifest') . '">';
        $icons[] = '<link rel="mask-icon" href="' . $addon->getAssetsUrl('icons/safari-pinned-tab.svg') . '" color="' . $themeColor . '">';
        $icons[] = '<meta name="msapplication-TileColor" content="#2d89ef">';
        $icons = implode("\n    ", $icons);
        $ep->setSubject($icons . $ep->getSubject());
    });

    // add theme-information to js-variable rex as rex.theme
    // (1) System-Settings (2) no systemforced mode: user-mode (3) fallback: "auto"
    $user = rex::getUser();
    $theme = (string) rex::getProperty('theme');
    if ('' === $theme && $user) {
        $theme = (string) $user->getValue('theme');
    }
    rex_view::setJsProperty('theme', $theme ?: 'auto');
}

if (rex::isSetup()) {
    return;
}

/** @var array{labelcolor: string, showlink: int} $config */
$config = $addon->getConfig();

if (rex::isBackend() && rex::getUser()) {
    /* Customizer Ergänzungen */
    rex_view::addCssFile($addon->getAssetsUrl('css/customizer.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/customizer.js'), [rex_view::JS_IMMUTABLE => true]);

    if ('' != $config['labelcolor']) {
        rex_view::setJsProperty('customizer_labelcolor', $config['labelcolor']);
    }
    if ($config['showlink']) {
        rex_view::setJsProperty(
            'customizer_showlink',
            '<h1 class="be-style-customizer-title"><a href="' . rex_url::frontend() . '" target="_blank" rel="noreferrer noopener"><span class="be-style-customizer-title-name">' . rex_escape(rex::getServerName()) . '</span><i class="fa fa-external-link"></i></a></h1>',
        );
    }
}
