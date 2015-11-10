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
    $this->getPath('vendor/font-awesome/fonts/fontawesome-webfont.woff2') => $this->getAssetsPath('fonts/fontawesome-webfont.woff2'),
    $this->getPath('vendor/font-awesome/fonts/FontAwesome.otf') => $this->getAssetsPath('fonts/FontAwesome.otf'),
    $this->getPath('vendor/perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js') => $this->getAssetsPath('javascripts/perfect-scrollbar.jquery.min.js'),
    $this->getPath('vendor/perfect-scrollbar/css/perfect-scrollbar.min.css') => $this->getAssetsPath('css/perfect-scrollbar.min.css'),
];
foreach ($files as $source => $destination) {
    rex_file::copy($source, $destination);
}
