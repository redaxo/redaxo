<?php
/**
 * media_manager Addon
 *
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @author jan.kristinus[at]redaxo[dot]de Jan Kristinus
 *
 * @package redaxo5
 */

$error = '';

if($error == '' && !rex_config::has('media_manager', 'jpg_quality'))
{
  rex_config::set('media_manager', 'jpg_quality', 85);
}

if ($error != '')
  $this->setProperty('installmsg', $error);
else
  $this->setProperty('install', true);
