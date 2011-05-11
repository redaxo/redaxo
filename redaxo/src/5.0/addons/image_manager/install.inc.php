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
 * @package redaxo5
 * @version svn:$Id$
 */

$error = '';

if($error == '' && !rex_config::has('image_manager', 'jpg_quality'))
{
  rex_config::set('image_manager', 'jpg_quality', 85);
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);