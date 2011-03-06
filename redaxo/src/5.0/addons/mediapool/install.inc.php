<?php 

/**
 * Mediapool Addon
 *
 * @author redaxo
 *
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

if ($error != '')
{
  $REX['ADDON']['installmsg']['mediapool'] = $error;
}else
{
  $REX['ADDON']['install']['mediapool'] = true;
  rex_generateAll();
}