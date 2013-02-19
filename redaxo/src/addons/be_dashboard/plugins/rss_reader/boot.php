<?php

/**
 * RSS Reader Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

$mypage = 'rss_reader';

// im backend und eingeloggt?
if (rex::isBackend() && rex::getUser()) {
    if (rex_request('page', 'string') == 'be_dashboard') {
        require_once __DIR__ . '/functions/function_reader.php';

        rex_extension::register(
            'DASHBOARD_COMPONENT',
            array(new rex_rss_reader_component(), 'registerAsExtension')
        );
    }
}
