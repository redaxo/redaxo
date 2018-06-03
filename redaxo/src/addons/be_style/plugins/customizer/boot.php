<?php

/**
 * REDAXO customizer.
 *
 * Codemirror by : http://codemirror.net/
 * Marijn Haverbeke <marijnh@gmail.com>
 */

if (rex::isBackend() && rex::getUser()) {
    $config = $this->getConfig();

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));

    if ($config['codemirror']) {
        // Codemirror + Theme-CSS
        rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/codemirror.css'));
        rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.css'));
        rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/theme/'.$config['codemirror_theme'].'.css'));
        if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
            rex_view::addCssFile($this->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.css'));
        }

        // Theme
        if ($config['codemirror_theme'] != '') {
            rex_view::setJsProperty('customizer_codemirror_defaulttheme', $config['codemirror_theme']);
        }

        // Codemirror
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/codemirror-compressed.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/display/fullscreen.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/selection/active-line.js'));

        // Codemirror-Addons
        if (isset($config['codemirror-tools']) && $config['codemirror-tools']) {
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/foldcode.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/foldgutter.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/brace-fold.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/xml-fold.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/indent-fold.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/markdown-fold.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/fold/comment-fold.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/edit/closebrackets.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/edit/matchtags.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/edit/matchbrackets.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/addon/mode/overlay.js'));
        }

        // Highlighters
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/xml/xml.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/htmlmixed/htmlmixed.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/htmlembedded/htmlembedded.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/javascript/javascript.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/css/css.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/clike/clike.js'));
        rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/php/php.js'));

        if (isset($config['codemirror-langs']) && $config['codemirror-langs']) {
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/markdown/markdown.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/textile/textile.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/gfm/gfm.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/yaml/yaml.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/yaml-frontmatter/yaml-frontmatter.js'));
            rex_view::addJsFile($this->getAssetsUrl('vendor/codemirror/mode/meta.js'));
        }
    }

    if ($config['labelcolor'] != '') {
        rex_view::setJsProperty('customizer_labelcolor', $config['labelcolor']);
    }

    if ($config['showlink']) {
        rex_view::setJsProperty('customizer_showlink', '<h1 class="be-style-customizer-title"><a href="'. rex::getServer() .'" target="_blank" rel="noreferrer noopener"><span class="be-style-customizer-title-name">' . rex::getServerName() . '</span><i class="fa fa-external-link"></i></a></h1>');
    }

    rex_view::addJsFile($this->getAssetsUrl('js/main.js'));
}
