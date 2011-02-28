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

if ($error == '')
{
  $REX['ADDON']['install']['mediapool'] = false;
  rex_generateAll();
}