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

$config_file = $REX['INCLUDE_PATH'] .'/addons/cronjob/config.inc.php';

if(($state = rex_is_writable($config_file)) !== true)
  $error .= $state;

$log_folder = $REX['INCLUDE_PATH'] .'/addons/cronjob/logs/';

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