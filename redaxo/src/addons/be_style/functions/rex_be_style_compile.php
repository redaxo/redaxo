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
    $scssFiles = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_COMPILE', []));
    foreach ($scssFiles as $file) {
        $compiler = new rex_scss_compiler();

        if (isset($file['root_dir'])) {
            $compiler->setRootDir($file['root_dir']);
        }
        $compiler->setScssFile($file['scss_files']);

        // Compile in backend assets dir
        $compiler->setCssFile($file['css_file']);
        $compiler->compile();

        // Compiled file to copy in frontend assets dir
        if (isset($file['copy_dest'])) {
            rex_file::copy($file['css_file'], $file['copy_dest']);
        }
    }
}
