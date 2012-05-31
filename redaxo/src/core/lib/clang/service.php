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
   * @throws rex_exception
   */
  static public function addCLang($id, $code, $name)
  {
    if (rex_clang::exists($id))
      throw new rex_exception('clang with id "' . $id . '" already exists');

    $newLang = rex_sql::factory();
    $newLang->setTable(rex::getTablePrefix() . 'clang');
    $newLang->setValue('id', $id);
    $newLang->setValue('code', $code);
    $newLang->setValue('name', $name);
    $newLang->insert();

    rex_deleteCache();

    // ----- EXTENSION POINT
    $clang = rex_clang::get($id);
    rex_extension::registerPoint('CLANG_ADDED', '', array(
      'id'    => $clang->getId(),
      'name'  => $clang->getName(),
      'clang' => $clang
    ));
  }

  /**
   * Ändert eine Clang
   *
   * @param $id   Id der Clang
   * @param $code Clang Code
   * @param $name Name der Clang
   *
   * @throws rex_exception
   */
  static public function editCLang($id, $code, $name)
  {
    if (!rex_clang::exists($id))
      throw new rex_exception('clang with id "' . $id . '" does not exist');

    $editLang = rex_sql::factory();
    $editLang->setTable(rex::getTablePrefix() . 'clang');
    $editLang->setValue('id', $id);
    $editLang->setValue('code', $code);
    $editLang->setValue('name', $name);
    $editLang->update();

    rex_deleteCache();

    // ----- EXTENSION POINT
    $clang = rex_clang::get($id);
    rex_extension::registerPoint('CLANG_UPDATED', '', array(
      'id'    => $clang->getId(),
      'name'  => $clang->getName(),
      'clang' => $clang
    ));

    return true;
  }

  /**
   * Löscht eine Clang
   *
   * @param $id Zu löschende ClangId
   *
   * @throws rex_exception
   */
  static public function deleteCLang($id)
  {
    if ($id == 0)
      throw new rex_exception('clang with id "0" can not be deleted');

    if (!rex_clang::exists($id))
      throw new rex_exception('clang with id "' . $id . '" does not exist');

    $clang = rex_clang::get($id);

    $del = rex_sql::factory();
    $del->setQuery('delete from ' . rex::getTablePrefix() . "clang where id='$id'");

    rex_deleteCache();

    // ----- EXTENSION POINT
    rex_extension::registerPoint('CLANG_DELETED', '', array(
      'id'    => $clang->getId(),
      'name'  => $clang->getName(),
      'clang' => $clang
    ));
  }

  /**
   * Schreibt Spracheigenschaften in die Datei include/clang.inc.php
   *
   * @throws rex_exception
   */
  static public function generateCache()
  {
    $lg = rex_sql::factory();
    $lg->setQuery('select * from ' . rex::getTablePrefix() . 'clang order by id');

    $clangs = array();
    foreach ($lg as $lang) {
      $id = $lang->getValue('id');
      foreach ($lg->getFieldnames() as $field) {
        $clangs[$id][$field] = $lang->getValue($field);
      }
    }

    $file = rex_path::cache('clang.cache');
    if (rex_file::putCache($file, $clangs) === false) {
      throw new rex_exception('Clang cache file could not be generated');
    }
  }
}
