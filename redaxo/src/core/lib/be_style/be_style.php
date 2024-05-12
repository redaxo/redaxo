<?php

use Redaxo\Core\ExtensionPoint\Extension;
use Redaxo\Core\ExtensionPoint\ExtensionPoint;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;

class rex_be_style
{
    /**
     * Converts Backend SCSS files to CSS.
     * @return void
     */
    public static function compile()
    {
        $scssFiles = Extension::registerPoint(new ExtensionPoint('BE_STYLE_SCSS_FILES', []));

        /** @var list<array{root_dir?: string, scss_files: string|list<string>, css_file: string, copy_dest?: string}> */
        $scssFiles = [
            [
                'root_dir' => Path::core('assets_files/scss/'),
                'scss_files' => array_merge($scssFiles, [Path::core('assets_files/scss/master.scss')]),
                'css_file' => Path::core('assets/css/styles.css'),
                'copy_dest' => Path::coreAssets('css/styles.css'),
            ],
            [
                'root_dir' => Path::core('assets_files/scss/'),
                'scss_files' => Path::core('assets_files/scss/redaxo.scss'),
                'css_file' => Path::core('assets/css/redaxo.css'),
                'copy_dest' => Path::coreAssets('css/redaxo.css'),
            ],
        ];

        $scssFiles = Extension::registerPoint(new ExtensionPoint('BE_STYLE_SCSS_COMPILE', $scssFiles));

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
                File::copy($file['css_file'], $file['copy_dest']);
            }
        }
    }
}
