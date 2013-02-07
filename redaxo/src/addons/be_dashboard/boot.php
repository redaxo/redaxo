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
if (rex::isBackend() && rex_be_controller::getCurrentPagePart(1) == 'be_dashboard') {
  rex_be_controller::addCssFile($this->getAssetsUrl('be_dashboard.css'));
  rex_be_controller::addJsFile($this->getAssetsUrl('be_dashboard.js'));
}
