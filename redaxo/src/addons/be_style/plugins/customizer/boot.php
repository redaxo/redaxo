<?php

/**
 * REDAXO customizer.
 *
 * Codemirror by : http://codemirror.net/
 * Marijn Haverbeke <marijnh@gmail.com>
 */

// Plugin-Config
$plugin = rex_plugin::get('be_style', 'customizer');
/** @var array{codemirror_theme: string, codemirror_darktheme: string, codemirror-selectors: string, codemirror-options: string, codemirror: int, codemirror-langs: int, codemirror-tools: int, labelcolor: string, showlink: int, codemirror-autoresize?: bool} $config */
$config = $plugin->getConfig();

/* Output CodeMirror-CSS */
if (rex::isBackend() && 'css' == rex_request('codemirror_output', 'string', '')) {
    rex_response::cleanOutputBuffers();
    header('Content-type: text/css');

    $filenames = [];
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/codemirror.min.css');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.css');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/theme/'.$config['codemirror_theme'].'.css');
    if ('' != rex_request('themes', 'string', '')) {
        $themes = explode(',', rex_request('themes', 'string', ''));
        foreach ($themes as $theme) {
            if (preg_match('/[a-z0-9\._-]+/i', $theme)) {
                $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/theme/'.$theme.'.css');
            }
        }
    }
    if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.css');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/dialog/dialog.css');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/search/matchesonscrollbar.css');
    }
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/codemirror-additional.css');
    if (isset($config['codemirror-autoresize']) && $config['codemirror-autoresize']) {
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/codemirror-autoresize.css');
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
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/codemirror.min.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/display/autorefresh.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/selection/active-line.js');

    if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/foldcode.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/brace-fold.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/xml-fold.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/indent-fold.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/markdown-fold.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/fold/comment-fold.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/edit/closebrackets.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/edit/matchtags.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/edit/matchbrackets.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/mode/overlay.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/dialog/dialog.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/search/searchcursor.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/search/search.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/scroll/annotatescrollbar.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/search/matchesonscrollbar.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/addon/search/jump-to-line.js');
    }

    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/xml/xml.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/htmlmixed/htmlmixed.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/htmlembedded/htmlembedded.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/javascript/javascript.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/css/css.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/clike/clike.js');
    $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/php/php.js');

    if (isset($config['codemirror-langs']) && $config['codemirror-langs']) {
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/markdown/markdown.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/textile/textile.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/gfm/gfm.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/yaml/yaml.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/meta.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/properties/properties.js');
        $filenames[] = $plugin->getAssetsUrl('vendor/codemirror/mode/sql/sql.js');
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
        $mtimejs = filemtime($plugin->getAssetsUrl('vendor/codemirror/codemirror.min.js'));
        $mtimecss = filemtime($plugin->getAssetsUrl('vendor/codemirror/codemirror.min.css'));
        if (isset($_SESSION['codemirror_reload'])) {
            $mtimejs .= $_SESSION['codemirror_reload'];
            $mtimecss .= $_SESSION['codemirror_reload'];
        }
        rex_view::setJsProperty('customizer_codemirror_jsbuster', $mtimejs);
        rex_view::setJsProperty('customizer_codemirror_cssbuster', $mtimecss);
    }

    /* Customizer Ergänzungen */
    rex_view::addCssFile($plugin->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($plugin->getAssetsUrl('js/main.js'), [rex_view::JS_IMMUTABLE => true]);

    if ('' != $config['labelcolor']) {
        rex_view::setJsProperty('customizer_labelcolor', $config['labelcolor']);
    }
    if ($config['showlink']) {
        rex_view::setJsProperty(
            'customizer_showlink',
            '<h1 class="be-style-customizer-title"><a href="'. rex_url::frontend() .'" target="_blank" rel="noreferrer noopener"><span class="be-style-customizer-title-name">' . rex_escape(rex::getServerName()) . '</span><i class="fa fa-external-link"></i></a></h1>',
        );
    }
}
