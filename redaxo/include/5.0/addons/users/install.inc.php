<?php

/**
 * User Management
 *
 * @author 
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

if ($error != '')
  $REX['ADDON']['installmsg']['users'] = $error;
else
  $REX['ADDON']['install']['users'] = true;