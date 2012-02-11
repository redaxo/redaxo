<?php

/**
 * Cronjob Addon
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo5
 */

$error = '';

rex_dir::delete($this->getDataPath());

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', false);
