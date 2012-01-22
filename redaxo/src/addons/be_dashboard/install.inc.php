<?php

/**
 * Backenddashboard Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author <a href="http://www.redaxo.org">www.redaxo.org</a>
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

if($error == '')
{
  $file = dirname(__FILE__) .'/settings';

  if(($state = rex_is_writable($file)) !== true)
    $error = $state;
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);