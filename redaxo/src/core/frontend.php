<?php

/**
 *
 * @package redaxo5
 */

if (rex::isSetup()) {
  rex_response::sendRedirect(rex_url::backendController());
}

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.php');

// ----- caching end für output filter
$CONTENT = ob_get_contents();
ob_end_clean();

// trigger api functions
rex_api_function::handleCall();

if (rex_extension::isRegistered('FE_OUTPUT')) {
  // ----- EXTENSION POINT
  rex_extension::registerPoint('FE_OUTPUT', $CONTENT);
} else {
  // ----- inhalt ausgeben
  rex_response::sendPage($CONTENT);
}
