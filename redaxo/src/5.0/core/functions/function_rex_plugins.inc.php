<?php

/**
 * Addon Funktionen
 * @package redaxo4
 * @version svn:$Id$
 */

function rex_plugins_folder($addon, $plugin = null)
{
  if($plugin)
  {
    return rex_path::plugin($addon, $plugin);
  }

  return rex_path::addon($addon, 'plugins/');
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
    $folder = rex_path::plugin($addon, '*');
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
