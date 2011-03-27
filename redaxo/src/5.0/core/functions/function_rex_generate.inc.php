<?php

// ----------------------------------------- Alles generieren

/**
 * Löscht den vollständigen Artikel-Cache.
 */
function rex_generateAll()
{
  global $REX;

  // ----------------------------------------------------------- generated löschen
  rex_dir::deleteFiles(rex_path::generated());

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
