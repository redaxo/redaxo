<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo5
 */

if (!$this->hasConfig()) {
  $this->setConfig('from',     'from@example.com');
  $this->setConfig('fromname', 'Mailer');
  $this->setConfig('confirmto', '');
  $this->setConfig('mailer',   'sendmail');
  $this->setConfig('host',     'localhost');
  $this->setConfig('charset',  'utf-8');
  $this->setConfig('wordwrap', 120);
  $this->setConfig('encoding', '8bit');
  $this->setConfig('priority', 3);
  $this->setConfig('smtpauth', false);
  $this->setConfig('username', '');
  $this->setConfig('password', '');
}
