<?php
/**
 * Functions for redaxo.
 *
 * @package redaxo\be_style\redaxo
 */

/**
 * Converts Backend SCSS files to CSS.
 */
function rex_be_style_redaxo_compile()
{
    $rexPlugin = rex_plugin::get('be_style', 'redaxo');

    $compiler = new rex_scss_compiler();
    $compiler->setRootDir($rexPlugin->getPath('scss/'));
    $compiler->setScssFile($rexPlugin->getPath('scss/master.scss'));

    // Compile in backend assets dir
    $compiler->setCssFile($rexPlugin->getPath('assets/css/styles.css'));

    $compiler->compile();

    // Compiled file to copy in frontend assets dir
    rex_file::copy($rexPlugin->getPath('assets/css/styles.css'), $rexPlugin->getAssetsPath('css/styles.css'));
}
