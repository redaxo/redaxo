<?php

use Redaxo\Core\Translation\I18n;

echo rex_view::title(I18n::msg('media_manager'));

$func = rex_request('func', 'string');
if ('clear_cache' == $func) {
    $c = rex_media_manager::deleteCache();
    echo rex_view::info(I18n::msg('media_manager_cache_files_removed', $c));
}

rex_be_controller::includeCurrentPageSubPath();
