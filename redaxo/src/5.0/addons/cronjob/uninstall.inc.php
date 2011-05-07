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
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', false);