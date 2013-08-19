<?php

/**
 * Media Manager Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]yakmara[dot]de Jan Kristinus
 * @author dh[at]daveholloway[dot]co[dot]uk Dave Holloway
 *
 * @package redaxo5
 */

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');

echo rex_view::title(rex_i18n::msg("media_manager"));

if ($func == 'clear_cache') {
    $c = rex_media_manager::deleteCache();
    echo rex_view::info(rex_i18n::msg('media_manager_cache_files_removed', $c));
    $func = '';
}

include rex_be_controller::getCurrentPageObject()->getSubPath();
