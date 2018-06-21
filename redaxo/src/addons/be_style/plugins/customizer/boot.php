<?php
/**
 * REDAXO customizer.
 *
 * Codemirror by : http://codemirror.net/
 * Marijn Haverbeke <marijnh@gmail.com>
 */

// Plugin-Config
$config = rex_plugin::get('be_style', 'customizer')->getConfig();

/* Output CodeMirror-CSS */
if (rex::isBackend() && rex_request('codemirror_output', 'string', '') == 'css') {
    rex_response::cleanOutputBuffers();
    header('Content-type: text/css');

    $filenames = array();
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/codemirror.css');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.css');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/theme/'.$config['codemirror_theme'].'.css');
    if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.css');
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
if (rex::isBackend() && rex_request('codemirror_output', 'string', '') == 'javascript') {
    rex_response::cleanOutputBuffers();
    header('Content-Type: application/javascript');

    $filenames = array();
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/codemirror-compressed.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/selection/active-line.js');

    if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/foldcode.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/brace-fold.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/xml-fold.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/indent-fold.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/markdown-fold.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/fold/comment-fold.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/edit/closebrackets.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/edit/matchtags.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/edit/matchbrackets.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/addon/mode/overlay.js');
    }

    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/xml/xml.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/htmlmixed/htmlmixed.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/htmlembedded/htmlembedded.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/javascript/javascript.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/css/css.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/clike/clike.js');
    $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/php/php.js');

    if (isset($config['codemirror-langs']) && $config['codemirror-langs']) {
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/markdown/markdown.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/textile/textile.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/gfm/gfm.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/yaml/yaml.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.js');
        $filenames[] = $this->getAssetsUrl('vendor/codemirror/mode/meta.js');
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
        if ($config['codemirror_theme'] != '') {
            rex_view::setJsProperty('customizer_codemirror_defaulttheme', $config['codemirror_theme']);
        }
        // JsProperty CodeMirror-Selectors
        if ($config['codemirror-selectors'] != '') {
            rex_view::setJsProperty('customizer_codemirror_selectors', $config['codemirror-selectors']);
        }
        $mtime = filemtime($this->getAssetsUrl('vendor/codemirror/codemirror-compressed.js'));
        rex_view::setJsProperty('customizer_codemirror_jsbuster', $mtime);
        $mtime = filemtime($this->getAssetsUrl('vendor/codemirror/codemirror.css'));
        rex_view::setJsProperty('customizer_codemirror_cssbuster', $mtime);
    }

    /* Customizer Ergänzungen */
    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/main.js'));

    if ($config['labelcolor'] != '') {
        rex_view::setJsProperty('customizer_labelcolor', $config['labelcolor']);
    }
    if ($config['showlink']) {
        rex_view::setJsProperty('customizer_showlink', '<h1 class="be-style-customizer-title"><a href="'. rex::getServer() .'" target="_blank" rel="noreferrer noopener"><span class="be-style-customizer-title-name">' . rex::getServerName() . '</span><i class="fa fa-external-link"></i></a></h1>');
    }
}
