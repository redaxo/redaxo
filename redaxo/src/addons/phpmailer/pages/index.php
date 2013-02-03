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
$subpage = rex_be_controller::getCurrentPagePart(2);
$func = rex_request('func', 'string');

echo rex_view::title($this->i18n('title'));

switch ($subpage) {
    case 'example':
        require __DIR__ . '/example.php';
    break;
    default:
        require __DIR__ . '/settings.php';
}
