<?php

/**
 * Backenddashboard Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

// im backend und eingeloggt?
if (rex::isBackend() && rex::getUser()) {
  if (rex_be_controller::getCurrentPagePart(1) == 'be_dashboard') {
    require_once __DIR__ . '/functions/function_dashboard.php';
    rex_extension::register('PAGE_HEADER', 'rex_a655_add_assets');
  }
}
