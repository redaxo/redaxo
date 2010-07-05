<?php
/**
 * Image-Resize Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author <a href="http://www.vscope.at">www.vscope.at</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * 
 *
 * @package redaxo4
 * @version svn:$Id$
 */

$error = '';

if (!extension_loaded('gd'))
{
  $error = 'GD-LIB-extension not available! See <a href="http://www.php.net/gd">http://www.php.net/gd</a>';
}

if($error == '')
{
  $file = $REX['INCLUDE_PATH'] .'/addons/image_resize/config.inc.php';

  if(($state = rex_is_writable($file)) !== true)
    $error = $state;
}

if($error == '')
{
  $file = $REX['INCLUDE_PATH'] .'/generated/files';

  if(($state = rex_is_writable($file)) !== true)
    $error = $state;
}

if ($error != '')
  $REX['ADDON']['installmsg']['image_resize'] = $error;
else
  $REX['ADDON']['install']['image_resize'] = true;