<?php
/**
 * Functions for be_style.
 *
 * @package redaxo\be_style
 */

/**
 * Converts Backend SCSS files to CSS.
 */
function rex_be_style_compile()
{
    $beStyleAddon = rex_addon::get('be_style');

    $compiler = new rex_scss_compiler();

    $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', [$beStyleAddon->getPath('scss/master.scss')]));
    $compiler->setScssFile($scss_files);
    //$compiler->setScssFile($this->getPath('scss/master.scss'));

    // Compile in backend assets dir
    $compiler->setCssFile($beStyleAddon->getPath('assets/css/styles.css'));

    $compiler->compile();

    // Compiled file to copy in frontend assets dir
    rex_file::copy($beStyleAddon->getPath('assets/css/styles.css'), $beStyleAddon->getAssetsPath('css/styles.css'));
}
