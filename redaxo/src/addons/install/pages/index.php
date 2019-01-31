<?php

$addon = rex_addon::get('install');

echo rex_view::title($addon->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

rex_be_controller::includeCurrentPageSubPath();
