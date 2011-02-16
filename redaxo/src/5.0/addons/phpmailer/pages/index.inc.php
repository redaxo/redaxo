<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo4
 * @version svn:$Id$
 */

// Parameter
$Basedir = dirname(__FILE__);

$page = rex_request('page', 'string');
$subpage = rex_request('subpage', 'string');
$func = rex_request('func', 'string');

$subpages = array(
  array('',$REX['I18N']->msg('phpmailer_configuration')),
  array('example',$REX['I18N']->msg('phpmailer_example')),
);

rex_title($REX['I18N']->msg('phpmailer_title'), $subpages);

switch($subpage)
{
    case 'example':
        require $Basedir .'/example.inc.php';
    break;
    default:
        require $Basedir .'/settings.inc.php';
}