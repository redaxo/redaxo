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

rex_deleteDir(rex_path::addonData('cronjob'), true);

if ($error != '')
  $REX['ADDON']['installmsg']['cronjob'] = $error;
else
  $REX['ADDON']['install']['cronjob'] = false;