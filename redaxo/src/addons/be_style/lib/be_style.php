<?php
/**
 * @package redaxo\be-style
 *
 * @author bloep
 */
class rex_be_style
{
    /**
     * Converts Backend SCSS files to CSS.
     */
    public static function compile()
    {
        /** @var list<array{root_dir?: string, scss_files: string|list<string>, css_file: string, copy_dest?: string}> */
        $scssFiles = [];
        $scssFiles = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_COMPILE', $scssFiles));

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
            // When a release is created, the files are copied to the frontend assets dir
            // remember, the frontend assets dir is excluded by .gitignore
            if (isset($file['copy_dest'])) {
                rex_file::copy($file['css_file'], $file['copy_dest']);
            }
        }

        $addon = rex_addon::get('be_style');

        // use path relative to __DIR__ to get correct path in update temp dir
        $files = require __DIR__ . '/../vendor_files.php';

        // copy all vendor files because the new css file could depend on it
        // also useful for the command when CI wants to refresh the frontend assets
        foreach ($files as $source => $destination) {
            // ignore errors, because this file is included very early in setup, before the regular file permissions check
            rex_file::copy(__DIR__ . '/../' . $source, $addon->getAssetsPath($destination));
        }
    }
}
