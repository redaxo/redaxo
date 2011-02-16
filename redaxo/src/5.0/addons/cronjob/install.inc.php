<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

$log_folder = rex_path::addonData('cronjob');

rex_createDir($log_folder);

if(($state = rex_is_writable($log_folder)) !== true)
  $error .= $state;

if($error == '' && !rex_config::has('cronjob', 'nexttime'))
{
  rex_config::set('cronjob', 'nexttime', 0);
}

if ($error != '')
  $REX['ADDON']['installmsg']['cronjob'] = $error;
else
  $REX['ADDON']['install']['cronjob'] = true;