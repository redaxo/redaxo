<?php

// ----------------------------------------- Alles generieren

/**
 * Löscht den vollständigen Artikel-Cache und generiert den clang-cache
 */
function rex_generateAll()
{
  global $REX;
  
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

  rex_dir::deleteFiles(rex_path::cache());
  
  rex_logger::register();
}