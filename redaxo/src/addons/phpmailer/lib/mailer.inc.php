<?php

/**
 * PHPMailer Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 *
 * @package redaxo5
 */

class rex_mailer extends PHPMailer
{
  public function __construct()
  {
    $addon = rex_addon::get('phpmailer');

    $this->From             = $addon->getConfig('from');
    $this->FromName         = $addon->getConfig('fromname');
    $this->ConfirmReadingTo = $addon->getConfig('confirmto');
    $this->Mailer           = $addon->getConfig('mailer');
    $this->Host             = $addon->getConfig('host');
    $this->CharSet          = $addon->getConfig('charset');
    $this->WordWrap         = $addon->getConfig('wordwrap');
    $this->Encoding         = $addon->getConfig('encoding');
    $this->Priority         = $addon->getConfig('priority');
    $this->SMTPAuth         = $addon->getConfig('smtpauth');
    $this->Username         = $addon->getConfig('username');
    $this->Password         = $addon->getConfig('password');

    $this->PluginDir = $addon->getBasePath('lib/phpmailer/');
  }
}
