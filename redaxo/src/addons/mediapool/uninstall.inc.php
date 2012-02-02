<?php

/**
 * Mediapool Addon
 *
 * @author redaxo
 *
 * @package redaxo5
 */

$error = '';

if ($error == '')
{
  $this->setProperty('install', false);
  rex_generateAll();
}