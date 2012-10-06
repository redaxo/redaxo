<?php

use Symfony\Component\Finder\Finder;

/**
 * Functions
 * @package redaxo5
 */

/**
 * Deletes the cache
 */
function rex_deleteCache()
{
  // close logger, so the logfile can also be deleted
  rex_logger::close();

  $it = Finder::create()
    ->notName('.htaccess')
    ->notName('_readme.txt')
    ->in(rex_path::cache());

  rex_dir::deleteIterator($it, false);

  rex_clang::reset();

  // ----- EXTENSION POINT
  return rex_extension::registerPoint('CACHE_DELETED', rex_i18n::msg('delete_cache_message'));
}

function rex_ini_get($val)
{
  $val = trim(ini_get($val));
  if ($val != '') {
    $last = strtolower($val{
      strlen($val) - 1
    });
  } else {
    $last = '';
  }
  switch ($last) {
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
