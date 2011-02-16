<?php
/**
 * image_manager Addon
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

if($error == '')
{
  $file = rex_path::generated('files');

  if(($state = rex_is_writable($file)) !== true)
    $error = $state;
}

if($error == '' && !rex_config::has('image_manager', 'jpg_quality'))
{
  rex_config::set('image_manager', 'jpg_quality', 85);
}

if ($error != '')
  $REX['ADDON']['installmsg']['image_manager'] = $error;
else
  $REX['ADDON']['install']['image_manager'] = true;