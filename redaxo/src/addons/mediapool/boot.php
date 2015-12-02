<?php

/**
 * Mediapool Addon.
 *
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 *
 * @var rex_addon $this
 */

$mypage = 'mediapool';

rex_complex_perm::register('media', 'rex_media_perm');

if (rex::isUserLoggedIn()) {
    require_once __DIR__ . '/functions/function_rex_mediapool.php';

    rex_view::addJsFile($this->getAssetsUrl('mediapool.js'));
    rex_view::setJsProperty('imageExtensions', $this->getProperty('image_extensions'));
}
