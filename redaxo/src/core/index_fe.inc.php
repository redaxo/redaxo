<?php

/**
 *
 * @package redaxo5
 */

if (rex::isSetup()) {
  rex_response::sendRedirect('redaxo/');
}

// Setzten des arg_separators, falls Sessions verwendet werden,
// um XHTML valide Links zu produzieren
@ini_set('arg_separator.input', '&amp;');
@ini_set('arg_separator.output', '&amp;');

// ----- INCLUDE ADDONS
include_once rex_path::core('packages.inc.php');

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
  rex_response::sendArticle($CONTENT);
}
