<?php

/**
 * media_manager Addon.
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 */

$addon = rex_addon::get('media_manager');

if (!$addon->hasConfig('jpg_quality')) {
    $addon->setConfig('jpg_quality', 85);
}

if (!$addon->hasConfig('png_compression')) {
    $addon->setConfig('png_compression', 5);
}

if (!$addon->hasConfig('webp_quality')) {
    $addon->setConfig('webp_quality', 85);
}

if (!$addon->hasConfig('interlace')) {
    $addon->setConfig('interlace', ['jpg']);
}
