<?php

/**
 * Functions
 * @package redaxo5
 */

/**
 * Deletes the cache
 */
function rex_deleteCache()
{
  // unregister logger, so the logfile can also be deleted
  rex_logger::unregister();

  rex_dir::deleteIterator(rex_dir::recursiveIterator(rex_path::cache())->ignoreFiles(array('.htaccess', '_readme.txt'), false));

  rex_logger::register();

  rex_clang::reset();

  // ----- EXTENSION POINT
  return rex_extension::registerPoint('CACHE_DELETED', rex_i18n::msg('delete_cache_message'));
}

/**
 * Prüfen ob ein/e Datei/Ordner beschreibbar ist
 *
 * @access public
 * @param string $item Datei oder Verzeichnis
 * @return mixed true bei Erfolg, sonst Fehlermeldung
 */
function rex_is_writable($item)
{
  return _rex_is_writable_info(_rex_is_writable($item), $item);
}

function _rex_is_writable_info($is_writable, $item = '')
{
  $state = true;
  $key = '';
  switch($is_writable)
  {
    case 1:
    {
      $key = 'setup_012';
      break;
    }
    case 2:
    {
      $key = 'setup_014';
      break;
    }
    case 3:
    {
      $key = 'setup_015';
      break;
    }
  }

  if($key != '')
  {
    $file = '';
    if($item != '')
      $file = '<b>'. $item .'</b>';

    $state = rex_i18n::msg($key, '<span class="rex-error">', '</span>', rex_path::absolute($file));
  }

  return $state;
}

function _rex_is_writable($item)
{
  // Fehler unterdrücken, falls keine Berechtigung
  if (@ is_dir($item))
  {
    if (!@ is_writable($item . '/.'))
    {
      return 1;
    }
  }
  // Fehler unterdrücken, falls keine Berechtigung
  elseif (@ is_file($item))
  {
    if (!@ is_writable($item))
    {
      return 2;
    }
  }
  else
  {
    return 3;
  }

  return 0;
}

/**
 * Get the attribute $name out of $content. if the attribute is not defined $default is returned.
 *
 * @param string $name
 * @param string $content
 * @param mixed $default
 *
 * @return mixed the attribute with $name if existent, otherwise $default
 */
function rex_getAttributes($name,$content,$default = null)
{
  $prop = json_decode($content, true);
  if (isset($prop[$name])) return $prop[$name];
  return $default;
}

/**
 * Set the attribute $name to $value into $content.
 *
 * @param string $name
 * @param string $value
 * @param string $content
 *
 * @return string the encoded content
 */
function rex_setAttributes($name,$value,$content)
{
  $prop = json_decode($content, true);
  $prop[$name] = $value;
  return json_encode($prop);
}



function rex_ini_get($val)
{
  $val = trim(ini_get($val));
  if ($val != '') {
    $last = strtolower($val{strlen($val)-1});
  } else {
    $last = '';
  }
  switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
          $val *= 1024;
      case 'm':
          $val *= 1024;
      case 'k':
          $val *= 1024;
  }

  return $val;
}
