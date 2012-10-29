<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo5
 */

// Parameter
$subpage = rex_be_controller::getCurrentPagePart(1);
$func = rex_request('func', 'string');

echo rex_view::title($this->i18n('title'));

switch ($subpage) {
    case 'example':
        require __DIR__ . '/example.inc.php';
    break;
    default:
        require __DIR__ . '/settings.inc.php';
}
