<?php

/**
 * REDAXO Version Checker Addon
 * 
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.de">www.redaxo.de</a>
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

if($error == '')
{
  require_once dirname(__FILE__) .'/functions/function_version_check.inc.php';
  
  $url = 'http://www.redaxo.de';
  if(!rex_a657_open_http_socket($url, $errno, $errstr, 5))
  {
    $error .= 'The server was unable to connect to "'. $url .'".';
    $error .= 'Make sure the server has access to the internet.';
    if($error != '' || $errstr != '')
    {
      $error .= '(error '. $errno .'; '. $errstr .')';
    }
  }
}

if ($error != '')
  $REX['ADDON']['installmsg']['version_checker'] = $error;
else
  $REX['ADDON']['install']['version_checker'] = true;