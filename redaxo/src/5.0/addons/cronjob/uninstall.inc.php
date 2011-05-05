<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

rex_dir::delete(rex_path::addonData('cronjob'));

if ($error != '')
  $REX['ADDON']['installmsg']['cronjob'] = $error;
else
  $REX['ADDON']['install']['cronjob'] = false;