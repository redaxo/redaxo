<?php

/**
 * media_manager Addon.
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 */

$myaddon = rex_addon::get('media_manager');

if (!$myaddon->hasConfig('jpg_quality')) {
    $myaddon->setConfig('jpg_quality', 85);
}

if (!$myaddon->hasConfig('png_compression')) {
    $myaddon->setConfig('png_compression', 5);
}

if (!$myaddon->hasConfig('webp_quality')) {
    $myaddon->setConfig('webp_quality', 85);
}

if (!$myaddon->hasConfig('interlace')) {
    $myaddon->setConfig('interlace', ['jpg']);
}
