<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$REX['PERM'][] = 'phpmailer[]';

require_once($REX['INCLUDE_PATH']. '/addons/phpmailer/classes/class.phpmailer.php');
require_once($REX['INCLUDE_PATH']. '/addons/phpmailer/classes/class.rex_mailer.inc.php');