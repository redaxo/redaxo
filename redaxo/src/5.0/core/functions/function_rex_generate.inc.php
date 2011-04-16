<?php

// ----------------------------------------- Alles generieren

/**
 * Löscht den vollständigen Artikel-Cache.
 */
function rex_generateAll()
{
  global $REX;
  
  // unregister logger, so the logfile can also be deleted
  rex_logger::unregister();

  // ----------------------------------------------------------- generated löschen
  rex_dir::deleteFiles(rex_path::generated());
  
  rex_logger::register();

  // ----------------------------------------------------------- generiere clang
  if(($MSG = rex_clang_service::generateCache()) !== TRUE)
  {
    return $MSG;
  }

  // ----------------------------------------------------------- message
  $MSG = rex_i18n::msg('delete_cache_message');

  // ----- EXTENSION POINT
  $MSG = rex_register_extension_point('ALL_GENERATED', $MSG);

  return $MSG;
}
