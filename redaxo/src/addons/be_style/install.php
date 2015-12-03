<?php

/**
 * Backendstyle Addon.
 *
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo\be-style
 *
 * @var rex_addon $this
 */

$files = require __DIR__.'/vendor_files.php';

foreach ($files as $source => $destination) {
    rex_file::copy($this->getPath($source), $this->getAssetsPath($destination));
}
