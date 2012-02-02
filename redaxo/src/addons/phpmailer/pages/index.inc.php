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
$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

echo rex_view::title($this->i18n('title'));

switch($subpage)
{
    case 'example':
        require __DIR__ .'/example.inc.php';
    break;
    default:
        require __DIR__ .'/settings.inc.php';
}