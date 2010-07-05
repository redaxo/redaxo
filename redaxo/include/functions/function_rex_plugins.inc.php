<?php

/**
 * Addon Funktionen
 * @package redaxo4
 * @version svn:$Id$
 */

function rex_plugins_folder($addon, $plugin = null)
{
  $addonFolder = rex_addons_folder($addon);
  
  if($plugin)
    return $addonFolder. 'plugins' .DIRECTORY_SEPARATOR. $plugin .DIRECTORY_SEPARATOR;

  return $addonFolder. 'plugins' .DIRECTORY_SEPARATOR;
}

function rex_plugins_file()
{
  return (dirname(dirname(__FILE__))) . '/plugins.inc.php';
}

function rex_read_plugins_folder($addon, $folder = '')
{
  global $REX;
  $plugins = array ();

  if ($folder == '')
  {
    $folder = rex_plugins_folder($addon, '*');
  }
  
  $files = glob(rtrim($folder,DIRECTORY_SEPARATOR), GLOB_NOSORT);
  if(is_array($files))
  {
    foreach($files as $file)
    {
      $plugins[] = basename($file);
    }
  }
  
  // Sortiere Array
  natsort($plugins);
  
  return $plugins;
}
