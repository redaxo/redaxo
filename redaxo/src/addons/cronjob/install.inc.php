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

if(!$this->hasConfig('nexttime'))
{
  $this->setConfig('nexttime', 0);
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);