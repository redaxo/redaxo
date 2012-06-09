<?php

class rex_media_cache
{
  /**
   * Löscht die gecachte Medium-Datei.
   *
   * @param $filename Dateiname
   *
   * @return void
   */
  static public function delete($filename)
  {
    rex_file::delete(rex_path::addonCache('mediapool', $filename . '.media'));
    self::deleteLists();
  }

  /**
   * Löscht die gecachten Dateien der Media-Kategorie.
   *
   * @param $category_id Id der Media-Kategorie
   *
   * @return void
   */
  static public function deleteCategory($category_id)
  {
    rex_file::delete(rex_path::addonCache('mediapool', $category_id . '.mcat'));
    self::deleteCategoryLists();
  }

  /**
   * Löscht die gecachten Media-Listen.
   *
   * @return void
   */
  static public function deleteLists()
  {
    $cachePath = rex_path::addonCache('mediapool');

    $glob = glob($cachePath . '*.mlist');
    if (is_array($glob)) {
      foreach ($glob as $file) {
        rex_file::delete($file);
      }
    }

    $glob = glob($cachePath . '*.mextlist');
    if (is_array($glob)) {
      foreach ($glob as $file) {
        rex_file::delete($file);
      }
    }
  }

  /**
   * Löscht die gecachte Liste mit den Media der Kategorie.
   *
   * @param $category_id Id der Media-Kategorie
   *
   * @return void
   */
  static public function deleteList($category_id)
  {
    rex_file::delete(rex_path::addonCache('mediapool', $category_id . '.mlist'));
  }

  /**
   * Löscht die gecachten Media-Kategorien-Listen.
   *
   * @return void
   */
  static public function deleteCategoryLists()
  {
    $cachePath = rex_path::addonCache('mediapool');

    $glob = glob($cachePath . '*.mclist');
    if (is_array($glob)) {
      foreach ($glob as $file) {
        rex_file::delete($file);
      }
    }
  }

  /**
   * Löscht die gecachte Media-Kategorien-Liste.
   *
   * @param $category_id Id der Media-Kategorie
   *
   * @return void
   */
  static public function deleteCategoryList($category_id)
  {
    rex_file::delete(rex_path::addonCache('mediapool', $category_id . '.mclist'));
  }

  /**
   * Generiert den Cache des Mediums.
   *
   * @param $filename Dateiname des zu generierenden Mediums
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function generate($filename)
  {
    $query = 'SELECT * FROM ' . rex_media :: _getTableName() . ' WHERE filename = "' . $filename . '"';
    $sql = rex_sql::factory();
    //$sql->debugsql = true;
    $sql->setQuery($query);

    if ($sql->getRows() == 0) {
      return false;
    }

    $cacheArray = array();
    foreach ($sql->getFieldNames() as $fieldName) {
      $cacheArray[$fieldName] = $sql->getValue($fieldName);
    }

    $media_file = rex_path::addonCache('mediapool', $filename . '.media');
    if (rex_file::putCache($media_file, $cacheArray)) {
      return true;
    }

    return false;
  }

  /**
   * Generiert den Cache der Media-Kategorie.
   *
   * @param $category_id Id des zu generierenden Media-Kategorie
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function generateCategory($category_id)
  {
    // sanity check
    if ($category_id < 0) {
      return false;
    }

    $query = 'SELECT * FROM ' . rex_media_category :: _getTableName() . ' WHERE id = ' . $category_id;
    $sql = rex_sql::factory();
    //$sql->debugsql = true;
    $sql->setQuery($query);

    if ($sql->getRows() == 0) {
      return false;
    }

    $cacheArray = array();
    foreach ($sql->getFieldNames() as $fieldName) {
      $cacheArray[$fieldName] = $sql->getValue($fieldName);
    }

    $cat_file = rex_path::addonCache('mediapool', $category_id . '.mcat');
    if (rex_file::putCache($cat_file, $cacheArray)) {
      return true;
    }

    return false;
  }

  /**
   * Generiert eine Liste mit den Media einer Kategorie.
   *
   * @param $category_id Id der Kategorie
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function generateList($category_id)
  {
    // sanity check
    if ($category_id < 0) {
      return false;
    }

    $query = 'SELECT filename FROM ' . rex_media :: _getTableName() . ' WHERE category_id = ' . $category_id;
    $sql = rex_sql::factory();
    $sql->setQuery($query);

    $cacheArray = array();
    for ($i = 0; $i < $sql->getRows(); $i++) {
      $cacheArray[] = $sql->getValue('filename');
      $sql->next();
    }

    $list_file = rex_path::addonCache('mediapool', $category_id . '.mlist');
    if (rex_file::putCache($list_file, $cacheArray)) {
      return true;
    }

    return false;
  }

  /**
   * Generiert eine Liste mit den Kindkategorien einer Kategorie.
   *
   * @param $category_id Id der Kategorie
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function generateCategoryList($category_id)
  {
    // sanity check
    if ($category_id < 0) {
      return false;
    }

    $query = 'SELECT id, cast( name AS SIGNED ) AS sort FROM ' . rex_media_category :: _getTableName() . ' WHERE re_id = ' . $category_id . ' ORDER BY sort, name';
    $sql = rex_sql::factory();
    //$sql->debugsql = true;
    $sql->setQuery($query);

    $cacheArray = array();
    for ($i = 0; $i < $sql->getRows(); $i++) {
      $cacheArray[] = $sql->getValue('id');
      $sql->next();
    }

    $list_file = rex_path::addonCache('mediapool', $category_id . '.mclist');
    if (rex_file::putCache($list_file, $cacheArray)) {
      return true;
    }

    return false;
  }

  /**
   * Generiert eine Liste mit allen Media einer Dateiendung
   *
   * @param $extension Dateiendung der zu generierenden Liste
   *
   * @return TRUE bei Erfolg, sonst FALSE
   */
  static public function generateExtensionList($extension)
  {
    $query = 'SELECT filename FROM ' . rex_media :: _getTableName() . ' WHERE LOWER(RIGHT(filename, LOCATE(".", REVERSE(filename))-1)) = "' . strtolower($extension) . '"';
    $sql = rex_sql::factory();
    $sql->setQuery($query);

    $cacheArray = array();
    for ($i = 0; $i < $sql->getRows(); $i++) {
      $cacheArray[] = $sql->getValue('filename');
      $sql->next();
    }

    $list_file = rex_path::addonCache('mediapool', $extension . '.mextlist');
    if (rex_file::putCache($list_file, $cacheArray)) {
      return true;
    }

    return false;
  }
}
