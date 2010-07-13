<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$mypage = 'phpmailer';

$REX['ADDON']['rxid'][$mypage] = '93';
$REX['ADDON']['name'][$mypage] = 'PHPMailer';
$REX['ADDON']['perm'][$mypage] = 'phpmailer[]';
$REX['ADDON']['version'][$mypage] = "1.3";
$REX['ADDON']['author'][$mypage] = "Markus Staab, Brent R. Matzelle";
$REX['ADDON']['supportpage'][$mypage] = "forum.redaxo.de";

$REX['PERM'][] = 'phpmailer[]';

if ($REX['REDAXO'])
{
  $I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');
}

require_once($REX['INCLUDE_PATH']. '/addons/phpmailer/classes/class.phpmailer.php');
require_once($REX['INCLUDE_PATH']. '/addons/phpmailer/classes/class.rex_mailer.inc.php');