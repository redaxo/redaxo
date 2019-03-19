<?php

/**
 * Backendstyle Addon.
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="https://www.yakamara.de">www.yakamara.de</a>
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="https://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo\be-style
 */

$addon = rex_addon::get('be_style');

$files = require __DIR__.'/vendor_files.php';

foreach ($files as $source => $destination) {
    if (rex_file::copy($addon->getPath($source), $addon->getAssetsPath($destination)) === false) {
        $addon->setProperty('installmsg', 'Unable to copy file from "'. $addon->getPath($source) .'" to "'. $addon->getAssetsPath($destination) .'"');
    }
}

