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

if (rex::isSetup()) {
    return;
}

// ---------------------------------- Codemirror ----------------------------------
// TODO

/**
 * REDAXO customizer.
 *
 * Codemirror by : http://codemirror.net/
 * Marijn Haverbeke <marijnh@gmail.com>
 */

// Plugin-Config
/** @var array{codemirror_theme: string, codemirror_darktheme: string, codemirror-selectors: string, codemirror-options: string, codemirror: int, codemirror-langs: int, codemirror-tools: int, labelcolor: string, showlink: int, codemirror-autoresize?: bool} $config */
$config = $addon->getConfig();

/* Output CodeMirror-CSS */
if (rex::isBackend() && 'css' == rex_request('codemirror_output', 'string', '')) {
    rex_response::cleanOutputBuffers();
    header('Content-type: text/css');

    $filenames = [];
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/codemirror.min.css');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.css');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/theme/' . $config['codemirror_theme'] . '.css');
    if ('' != rex_request('themes', 'string', '')) {
        $themes = explode(',', rex_request('themes', 'string', ''));
        foreach ($themes as $theme) {
            if (preg_match('/[a-z0-9\._-]+/i', $theme)) {
                $filenames[] = $addon->getAssetsUrl('vendor/codemirror/theme/' . $theme . '.css');
            }
        }
    }
    if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.css');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/dialog/dialog.css');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/search/matchesonscrollbar.css');
    }
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/codemirror-additional.css');
    if (isset($config['codemirror-autoresize']) && $config['codemirror-autoresize']) {
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/codemirror-autoresize.css');
    }

    $content = '';
    foreach ($filenames as $filename) {
        $content .= '/* ' . $filename . ' */' . "\n" . rex_file::get($filename) . "\n";
    }

    header('Pragma: cache');
    header('Cache-Control: public');
    header('Expires: ' . date('D, j M Y', strtotime('+1 week')) . ' 00:00:00 GMT');
    echo $content;

    exit;
}

/* Output CodeMirror-JavaScript */
if (rex::isBackend() && 'javascript' == rex_request('codemirror_output', 'string', '')) {
    rex_response::cleanOutputBuffers();
    header('Content-Type: application/javascript');

    $filenames = [];
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/codemirror.min.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/display/autorefresh.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/selection/active-line.js');

    if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/foldcode.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/brace-fold.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/xml-fold.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/indent-fold.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/markdown-fold.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/fold/comment-fold.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/edit/closebrackets.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/edit/matchtags.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/edit/matchbrackets.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/mode/overlay.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/dialog/dialog.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/search/searchcursor.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/search/search.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/scroll/annotatescrollbar.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/search/matchesonscrollbar.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/addon/search/jump-to-line.js');
    }

    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/xml/xml.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/htmlmixed/htmlmixed.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/htmlembedded/htmlembedded.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/javascript/javascript.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/css/css.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/clike/clike.js');
    $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/php/php.js');

    if (isset($config['codemirror-langs']) && $config['codemirror-langs']) {
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/markdown/markdown.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/textile/textile.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/gfm/gfm.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/yaml/yaml.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/meta.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/properties/properties.js');
        $filenames[] = $addon->getAssetsUrl('vendor/codemirror/mode/sql/sql.js');
    }

    $content = '';
    foreach ($filenames as $filename) {
        $content .= '/* ' . $filename . ' */' . "\n" . rex_file::get($filename) . "\n";
    }

    header('Pragma: cache');
    header('Cache-Control: public');
    header('Expires: ' . date('D, j M Y', strtotime('+1 week')) . ' 00:00:00 GMT');
    echo $content;

    exit;
}

if (rex::isBackend() && rex::getUser()) {
    /* Codemirror */
    if ($config['codemirror']) {
        // JsProperty CodeMirror-Theme
        rex_view::setJsProperty('customizer_codemirror_defaulttheme', $config['codemirror_theme']);
        rex_view::setJsProperty('customizer_codemirror_defaultdarktheme', $config['codemirror_darktheme'] ?? 'dracula');
        // JsProperty CodeMirror-Selectors
        $selectors = 'textarea.rex-code, textarea.rex-js-code, textarea.codemirror';
        if (isset($config['codemirror-selectors']) && '' != $config['codemirror-selectors']) {
            $selectors = $selectors . ', ' . $config['codemirror-selectors'];
        }
        rex_view::setJsProperty('customizer_codemirror_selectors', $selectors);
        // JsProperty CodeMirror-Autoresize
        if (isset($config['codemirror-autoresize'])) {
            rex_view::setJsProperty('customizer_codemirror_autoresize', $config['codemirror-autoresize']);
        }
        // JsProperty Codemirror-Options
        rex_view::setJsProperty('customizer_codemirror_options', str_replace(["\n", "\r"], '', trim($config['codemirror-options'] ?? '')));
        // JsProperty JS/CSS-Buster
        $mtimejs = filemtime($addon->getAssetsUrl('vendor/codemirror/codemirror.min.js'));
        $mtimecss = filemtime($addon->getAssetsUrl('vendor/codemirror/codemirror.min.css'));
        if (isset($_SESSION['codemirror_reload'])) {
            $mtimejs .= $_SESSION['codemirror_reload'];
            $mtimecss .= $_SESSION['codemirror_reload'];
        }
        rex_view::setJsProperty('customizer_codemirror_jsbuster', $mtimejs);
        rex_view::setJsProperty('customizer_codemirror_cssbuster', $mtimecss);
    }

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
        $icons[] = '<link rel="apple-touch-icon" sizes="180x180" href="' . $addon->getAssetsUrl(
            'icons/apple-touch-icon.png',
        ) . '">';
        $icons[] = '<link rel="icon" type="image/png" sizes="32x32" href="' . $addon->getAssetsUrl(
            'icons/favicon-32x32.png',
        ) . '">';
        $icons[] = '<link rel="icon" type="image/png" sizes="16x16" href="' . $addon->getAssetsUrl(
            'icons/favicon-16x16.png',
        ) . '">';
        $icons[] = '<link rel="manifest" href="' . $addon->getAssetsUrl('icons/site.webmanifest') . '">';
        $icons[] = '<link rel="mask-icon" href="' . $addon->getAssetsUrl(
            'icons/safari-pinned-tab.svg',
        ) . '" color="' . $themeColor . '">';
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
