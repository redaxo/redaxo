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

class rex_mailer extends PHPMailer
{
  public function __construct()
  {
    $this->From             = rex_config::get('phpmailer', 'from');
    $this->FromName         = rex_config::get('phpmailer', 'fromname');
    $this->ConfirmReadingTo = rex_config::get('phpmailer', 'confirmto');
    $this->Mailer           = rex_config::get('phpmailer', 'mailer');
    $this->Host             = rex_config::get('phpmailer', 'host');
    $this->CharSet          = rex_config::get('phpmailer', 'charset');
    $this->WordWrap         = rex_config::get('phpmailer', 'wordwrap');
    $this->Encoding         = rex_config::get('phpmailer', 'encoding');
    $this->Priority         = rex_config::get('phpmailer', 'priority');
    $this->SMTPAuth         = rex_config::get('phpmailer', 'smtpauth');
    $this->Username         = rex_config::get('phpmailer', 'username');
    $this->Password         = rex_config::get('phpmailer', 'password');

    $this->PluginDir = rex_path::addon('phpmailer', 'classes/');
  }

  public function SetLanguage($lang_type = 'de', $lang_path = null)
  {
    if ($lang_path == null)
      $lang_path = rex_path::addon('phpmailer', 'classes/language/');

    parent :: SetLanguage($lang_type, $lang_path);
  }
}