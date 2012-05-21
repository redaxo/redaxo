<?php

/**
 * REDAXO Version Checker Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 */

$error = '';

if ($error == '')
{
  require_once dirname(__FILE__) .'/functions/function_version_check.inc.php';

  $url = 'http://www.redaxo.org';
  if (!rex_a657_open_http_socket($url, $errno, $errstr, 5))
  {
    $error .= 'The server was unable to connect to "'. $url .'".';
    $error .= 'Make sure the server has access to the internet.';
    if ($error != '' || $errstr != '')
    {
      $error .= '(error '. $errno .'; '. $errstr .')';
    }
  }
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);
