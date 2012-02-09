<?php

/**
 * Mediapool Addon
 *
 * @author redaxo
 *
 * @package redaxo5
 */

$error = '';

if ($error != '')
  $this->setProperty('installmsg', $error);
else
{
  $this->setProperty('install', true);
  rex_deleteCache();
}