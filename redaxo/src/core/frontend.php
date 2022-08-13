<?php

if (rex::isSetup()) {
    rex_response::sendRedirect(rex_url::backendController());
}

if (rex::isDebugMode()) {
    header('X-Robots-Tag: noindex, nofollow, noarchive');
}

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.php');

// ----- caching end für output filter
$CONTENT = ob_get_clean();

// trigger api functions. the api function is responsible for checking permissions.
rex_api_function::handleCall();

if (rex_extension::isRegistered('FE_OUTPUT')) {
    // ----- EXTENSION POINT
    rex_extension::registerPoint(new rex_extension_point('FE_OUTPUT', $CONTENT));
} else {
    // ----- inhalt ausgeben
    rex_response::sendPage($CONTENT);
}
