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

// use path relative to __DIR__ to get correct path in update temp dir
$files = require __DIR__.'/vendor_files.php';

foreach ($files as $source => $destination) {
    // ignore errors, because this file is included very early in setup, before the regular file permissions check
    rex_file::copy(__DIR__.'/'.$source, $addon->getAssetsPath($destination));
}
