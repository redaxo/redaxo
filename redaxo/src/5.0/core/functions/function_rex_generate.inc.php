<?php

// ----------------------------------------- Alles generieren

/**
 * Löscht den vollständigen Artikel-Cache und generiert den clang-cache
 */
function rex_generateAll()
{
  // ----------------------------------------------------------- generated löschen
  rex_deleteAll();

  // ----------------------------------------------------------- generiere clang
  if(($MSG = rex_clang_service::generateCache()) !== TRUE)
  {
    return $MSG;
  }

  // ----------------------------------------------------------- message
  $MSG = rex_i18n::msg('delete_cache_message');

  // ----- EXTENSION POINT
  $MSG = rex_extension::registerPoint('ALL_GENERATED', $MSG);

  return $MSG;
}

/**
 * Löscht den vollständigen Artikel-Cache.
 */
function rex_deleteAll()
{
  // unregister logger, so the logfile can also be deleted
  rex_logger::unregister();

  foreach(new FilesystemIterator(rex_path::cache(), FilesystemIterator::SKIP_DOTS) as $file)
  {
    if($file->isDir())
    {
      rex_dir::delete($file->getPathname());
    }
    elseif(!in_array($file->getFilename(), array('.htaccess', '_readme.txt')))
    {
      rex_file::delete($file->getPathname());
    }
  }

  rex_logger::register();
}