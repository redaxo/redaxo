<?php

$myaddon = rex_addon::get('install');

echo rex_view::title($myaddon->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

rex_be_controller::includeCurrentPageSubPath();
