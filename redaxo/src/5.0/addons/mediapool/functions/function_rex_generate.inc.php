<?php

/**
 * Löscht die gecachte Medium-Datei.
 *
 * @param $filename Dateiname
 *
 * @return void
 */
function rex_deleteCacheMedia($filename)
{
  rex_file::delete(rex_path::generated('files/'. $filename . '.media'));
  rex_deleteCacheMediaLists();
}

/**
 * Löscht die gecachten Dateien der Media-Kategorie.
 *
 * @param $category_id Id der Media-Kategorie
 *
 * @return void
 */
function rex_deleteCacheMediaCategory($category_id)
{
  rex_file::delete(rex_path::generated('files/'. $category_id . '.mcat'));
  rex_deleteCacheMediaCategoryLists();
}

/**
 * Löscht die gecachten Media-Listen.
 *
 * @return void
 */
function rex_deleteCacheMediaLists()
{
  $cachePath = rex_path::generated('files/');

  $glob = glob($cachePath . '*.mlist');
  if(is_array($glob))
    foreach ($glob as $file)
      rex_file::delete($file);

  $glob = glob($cachePath . '*.mextlist');
  if(is_array($glob))
    foreach ($glob as $file)
      rex_file::delete($file);
}

/**
 * Löscht die gecachte Liste mit den Media der Kategorie.
 *
 * @param $category_id Id der Media-Kategorie
 *
 * @return void
 */
function rex_deleteCacheMediaList($category_id)
{
  rex_file::delete(rex_path::generated('files/'. $category_id . '.mlist'));
}

/**
 * Löscht die gecachten Media-Kategorien-Listen.
 *
 * @return void
 */
function rex_deleteCacheMediaCategoryLists()
{
  $cachePath = rex_path::generated('files/');
  $glob = glob($cachePath . '*.mclist');
  if (is_array($glob))
    foreach ($glob as $file)
      rex_file::delete($file);
}

/**
 * Löscht die gecachte Media-Kategorien-Liste.
 *
 * @param $category_id Id der Media-Kategorie
 *
 * @return void
 */
function rex_deleteCacheMediaCategoryList($category_id)
{
  rex_file::delete(rex_path::generated('files/'. $category_id . '.mclist'));
}

/**
 * Generiert den Cache des Mediums.
 *
 * @param $filename Dateiname des zu generierenden Mediums
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMedia($filename)
{
  global $REX;

  $query = 'SELECT * FROM ' . rex_ooMedia :: _getTableName() . ' WHERE filename = "'.$filename.'"';
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);

  if ($sql->getRows() == 0)
    return false;

  $cacheArray = array();
  foreach($sql->getFieldNames() as $fieldName)
  {
    $cacheArray[$fieldName] = $sql->getValue($fieldName);
  }

  $media_file = rex_path::generated('files/'. $filename .'.media');
  if (rex_file::putCache($media_file, $cacheArray))
    return true;

  return false;
}

/**
 * Generiert den Cache der Media-Kategorie.
 *
 * @param $category_id Id des zu generierenden Media-Kategorie
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaCategory($category_id)
{
  global $REX;

  $query = 'SELECT * FROM ' . rex_ooMediaCategory :: _getTableName() . ' WHERE id = '.$category_id;
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);

  if ($sql->getRows() == 0)
    return false;

  $cacheArray = array();
  foreach($sql->getFieldNames() as $fieldName)
  {
    $cacheArray[$fieldName] = $sql->getValue($fieldName);
  }

  $cat_file = rex_path::generated('files/'. $category_id .'.mcat');
  if (rex_file::putCache($cat_file, $cacheArray))
    return true;

  return false;
}

/**
 * Generiert eine Liste mit den Media einer Kategorie.
 *
 * @param $category_id Id der Kategorie
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaList($category_id)
{
  global $REX;

  $query = 'SELECT filename FROM ' . rex_ooMedia :: _getTableName() . ' WHERE category_id = ' . $category_id;
  $sql = rex_sql::factory();
  $sql->setQuery($query);

  $cacheArray = array();
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $cacheArray[] = $sql->getValue('filename');
    $sql->next();
  }

  $list_file = rex_path::generated('files/'. $category_id .'.mlist');
  if (rex_file::putCache($list_file, $cacheArray))
    return true;

  return false;
}

/**
 * Generiert eine Liste mit den Kindkategorien einer Kategorie.
 *
 * @param $category_id Id der Kategorie
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaCategoryList($category_id)
{
  global $REX;

  $query = 'SELECT id, cast( name AS SIGNED ) AS sort FROM ' . rex_ooMediaCategory :: _getTableName() . ' WHERE re_id = ' . $category_id . ' ORDER BY sort, name';
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);

  $cacheArray = array();
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $cacheArray[] = $sql->getValue('id');
    $sql->next();
  }

  $list_file = rex_path::generated('files/'. $category_id .'.mclist');
  if (rex_file::putCache($list_file, $cacheArray))
    return true;

  return false;
}

/**
 * Generiert eine Liste mit allen Media einer Dateiendung
 *
 * @param $extension Dateiendung der zu generierenden Liste
 *
 * @return TRUE bei Erfolg, sonst FALSE
 */
function rex_generateMediaExtensionList($extension)
{
  global $REX;

  $query = 'SELECT filename FROM ' . rex_ooMedia :: _getTableName() . ' WHERE SUBSTRING(filename,LOCATE( ".",filename)+1) = "' . $extension . '"';
  $sql = rex_sql::factory();
  $sql->setQuery($query);

  $cacheArray = array();
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $cacheArray[] = $sql->getValue('filename');
    $sql->next();
  }

  $list_file = rex_path::generated('files/'. $extension .'.mextlist');
  if (rex_file::putCache($list_file, $cacheArray))
    return true;

  return false;
}