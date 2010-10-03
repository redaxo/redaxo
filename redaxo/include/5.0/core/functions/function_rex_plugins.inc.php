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
  {
    $pluginDir = $addonFolder. 'plugins' .DIRECTORY_SEPARATOR. $plugin .DIRECTORY_SEPARATOR;

    if($plugin != '*')
    {
      if(!is_dir($pluginDir))
      {
        throw new rexException('Expecting "'. $pluginDir .'" to be a directory!');
      }
      if(!is_writable($pluginDir))
      {
        throw new rexException('Expecting "'. $pluginDir .'" to be a directory with write permissions!');
      }
    }
    
    return $pluginDir;
  }

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
  
  return $plugins;
}
