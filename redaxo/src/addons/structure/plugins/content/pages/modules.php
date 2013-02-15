<?php
/**
 *
 * @package redaxo5
 */

// -------------- Defaults

$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');

echo rex_view::title(rex_i18n::msg('modules'));

include rex_be_controller::getCurrentPageObject()->getSubPath();
