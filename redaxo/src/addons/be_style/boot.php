<?php

/**
 * Backendstyle Addon.
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

$mypage = 'be_style';

/* Addon Parameter */
if (rex::isBackend()) {

    require_once rex_path::addon($mypage, 'extensions/function_extensions.php');
    rex_extension::register('PACKAGES_INCLUDED', 'rex_be_add_page');

    $files = [
        $this->getPath('vendor/bootstrap/assets/javascripts/bootstrap.js') => $this->getAssetsPath('javascripts/bootstrap.js'),
        $this->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.eot') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.eot'),
        $this->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.svg') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.svg'),
        $this->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.ttf') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.ttf'),
        $this->getPath('vendor/bootstrap/assets/fonts/bootstrap/glyphicons-halflings-regular.woff') => $this->getAssetsPath('fonts/bootstrap/glyphicons-halflings-regular.woff'),
        $this->getPath('vendor/font-awesome/fonts/fontawesome-webfont.eot') => $this->getAssetsPath('fonts/fontawesome-webfont.eot'),
        $this->getPath('vendor/font-awesome/fonts/fontawesome-webfont.svg') => $this->getAssetsPath('fonts/fontawesome-webfont.svg'),
        $this->getPath('vendor/font-awesome/fonts/fontawesome-webfont.ttf') => $this->getAssetsPath('fonts/fontawesome-webfont.ttf'),
        $this->getPath('vendor/font-awesome/fonts/fontawesome-webfont.woff') => $this->getAssetsPath('fonts/fontawesome-webfont.woff'),
        $this->getPath('vendor/font-awesome/fonts/FontAwesome.otf') => $this->getAssetsPath('fonts/FontAwesome.otf'),
    ];

    foreach ($files as $source => $destination) {
        if (!file_exists($destination)) {
            rex_file::copy($source, $destination);
        }
    }

    if (rex::getUser() && $this->getProperty('compile')) {

        rex_extension::register('PACKAGES_INCLUDED', function () {
            $compiler = new rex_scss_compiler();

            $scss_files = rex_extension::registerPoint(new rex_extension_point('BE_STYLE_SCSS_FILES', [$this->getPath('scss/master.scss')]));
            $compiler->setScssFile($scss_files);
            //$compiler->setScssFile($this->getPath('scss/master.scss'));

            // Compile in backend assets dir
            $compiler->setCssFile($this->getPath('assets/css/styles.css'));

            $compiler->compile();

            // Compiled file to copy in frontend assets dir
            rex_file::copy($this->getPath('assets/css/styles.css'), $this->getAssetsPath('css/styles.css'));

        });
    }

    rex_view::addCssFile($this->getAssetsUrl('css/styles.css'));
    rex_view::addJsFile($this->getAssetsUrl('javascripts/bootstrap.js'));
}
