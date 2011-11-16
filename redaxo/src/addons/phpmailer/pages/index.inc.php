<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo5
 * @version svn:$Id$
 */

// Parameter
$Basedir = dirname(__FILE__);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

$subpages = array(
  array('',rex_i18n::msg('phpmailer_configuration')),
  array('example',rex_i18n::msg('phpmailer_example')),
);

rex_title(rex_i18n::msg('phpmailer_title'), $subpages);

switch($subpage)
{
    case 'example':
        require $Basedir .'/example.inc.php';
    break;
    default:
        require $Basedir .'/settings.inc.php';
}