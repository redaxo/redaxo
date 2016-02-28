<?php

/** @var rex_addon $this */

echo rex_view::title($this->i18n('title'));

if ('reload' === rex_request('func', 'string')) {
    rex_install_webservice::deleteCache();
}

rex_be_controller::includeCurrentPageSubPath();
