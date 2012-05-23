<?php

class rex_clang_service
{
  /**
   * Erstellt eine Clang
   *
   * @param $id   Id der Clang
   * @param $code Clang Code
   * @param $name Name der Clang
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function addCLang($id, $code, $name)
  {
    if(rex_clang::exists($id))
      return FALSE;

    $newLang = rex_sql::factory();
    $newLang->setTable(rex::getTablePrefix()."clang");
    $newLang->setValue('id', $id);
    $newLang->setValue('code', $code);
    $newLang->setValue('name', $name);
    $newLang->insert();

    rex_deleteCache();

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_ADDED', '', array('clang' => rex_clang::get($id)));

    return TRUE;
  }

  /**
   * Ändert eine Clang
   *
   * @param $id   Id der Clang
   * @param $code Clang Code
   * @param $name Name der Clang
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function editCLang($id, $code, $name)
  {
    if(!rex_clang::exists($id))
      return false;

    $editLang = rex_sql::factory();
    $editLang->setTable(rex::getTablePrefix()."clang");
    $editLang->setValue('id', $id);
    $editLang->setValue('code', $code);
    $editLang->setValue('name', $name);
    $editLang->update();

    rex_deleteCache();

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_UPDATED', '', array('clang' => rex_clang::get($id)));

    return TRUE;
  }

  /**
   * Löscht eine Clang
   *
   * @param $id Zu löschende ClangId
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function deleteCLang($id)
  {
    if ($id == 0 || !rex_clang::exists($id))
      return FALSE;

    $clang = rex_clang::get($id);

    $del = rex_sql::factory();
    $del->setQuery("delete from ".rex::getTablePrefix()."clang where id='$id'");

    rex_deleteCache();

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_DELETED', '', array('clang' => $clang));

    return TRUE;
  }

  /**
   * Schreibt Spracheigenschaften in die Datei include/clang.inc.php
   *
   * @return TRUE bei Erfolg, sonst eine Fehlermeldung
   */
  static public function generateCache()
  {
    $lg = rex_sql::factory();
    $lg->setQuery("select * from ".rex::getTablePrefix()."clang order by id");

    $clangs = array();
    foreach($lg as $lang)
    {
      $id = $lang->getValue('id');
      foreach($lg->getFieldnames() as $field)
      {
        $clangs[$id][$field] = $lang->getValue($field);
      }
    }

    $file = rex_path::cache('clang.cache');
    if(rex_file::putCache($file, $clangs) === FALSE)
    {
      return 'Datei "'.$file.'" hat keine Schreibrechte';
    }
    return TRUE;
  }
}
