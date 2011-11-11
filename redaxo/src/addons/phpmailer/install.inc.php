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

$error = '';

if(!rex_config::has('phpmailer'))
{
  rex_config::set('phpmailer', 'from',     'from@example.com');
  rex_config::set('phpmailer', 'fromname', 'Mailer');
  rex_config::set('phpmailer', 'confirmto', '');
  rex_config::set('phpmailer', 'mailer',   'sendmail');
  rex_config::set('phpmailer', 'host',     'localhost');
  rex_config::set('phpmailer', 'charset',  'utf-8');
  rex_config::set('phpmailer', 'wordwrap', 120);
  rex_config::set('phpmailer', 'encoding', '8bit');
  rex_config::set('phpmailer', 'priority', 3);
  rex_config::set('phpmailer', 'smtpauth', false);
  rex_config::set('phpmailer', 'username', '');
  rex_config::set('phpmailer', 'password', '');
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);