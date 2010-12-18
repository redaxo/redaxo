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
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $filename . '.media');
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
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $category_id . '.mcat');
  rex_deleteCacheMediaCategoryLists();
}

/**
 * Löscht die gecachten Media-Listen.
 * 
 * @return void
 */
function rex_deleteCacheMediaLists()
{
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/files'. DIRECTORY_SEPARATOR;
  
  $glob = glob($cachePath . '*.mlist');
  if(is_array($glob))
    foreach ($glob as $file)
      @unlink($file);
  
  $glob = glob($cachePath . '*.mextlist');
  if(is_array($glob))
    foreach ($glob as $file)
      @unlink($file);
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
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $category_id . '.mlist');
}

/**
 * Löscht die gecachten Media-Kategorien-Listen.
 * 
 * @return void
 */
function rex_deleteCacheMediaCategoryLists()
{
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/files'. DIRECTORY_SEPARATOR;
  $glob = glob($cachePath . '*.mclist');
  if (is_array($glob))
    foreach ($glob as $file)
      @unlink($file);
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
  global $REX;
  
  $cachePath = $REX['SRC_PATH']. DIRECTORY_SEPARATOR .'generated/files'. DIRECTORY_SEPARATOR;
  @unlink($cachePath . $category_id . '.mclist');
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
  
  $query = 'SELECT * FROM ' . OOMedia :: _getTableName() . ' WHERE filename = "'.$filename.'"';
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);
  
  if ($sql->getRows() == 0)
    return false;
  
  $content = '<?php'."\n";
  foreach($sql->getFieldNames() as $fieldName)
  {
    $content .= '$REX[\'MEDIA\'][\'FILENAME\'][\''. $filename .'\'][\''. $fieldName .'\'] = \''. rex_addslashes($sql->getValue($fieldName),'\\\'') .'\';'."\n";
  }
  $content .= '?>';
  
  $media_file = $REX['SRC_PATH']."/generated/files/$filename.media";
  if (rex_put_file_contents($media_file, $content))
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
  
  $query = 'SELECT * FROM ' . OOMediaCategory :: _getTableName() . ' WHERE id = '.$category_id;
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);
  
  if ($sql->getRows() == 0)
    return false;
  
  $content = '<?php'."\n";
  foreach($sql->getFieldNames() as $fieldName)
  {
    $content .= '$REX[\'MEDIA\'][\'CAT_ID\']['. $category_id .'][\''. $fieldName .'\'] = \''. rex_addslashes($sql->getValue($fieldName),'\\\'') .'\';'."\n";
  }
  $content .= '?>';
  
  $cat_file = $REX['SRC_PATH']."/generated/files/$category_id.mcat";
  if (rex_put_file_contents($cat_file, $content))
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
  
  $query = 'SELECT filename FROM ' . OOMedia :: _getTableName() . ' WHERE category_id = ' . $category_id;
  $sql = rex_sql::factory();
  $sql->setQuery($query);
  
  $content = '<?php'."\n";
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $content .= '$REX[\'MEDIA\'][\'MEDIA_CAT_ID\']['. $category_id .']['. $i .'] = \''. $sql->getValue('filename') .'\';'."\n";
    $sql->next();
  }
  $content .= '?>';
  
  $list_file = $REX['SRC_PATH']."/generated/files/$category_id.mlist";
  if (rex_put_file_contents($list_file, $content))
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
  
  $query = 'SELECT id, cast( name AS SIGNED ) AS sort FROM ' . OOMediaCategory :: _getTableName() . ' WHERE re_id = ' . $category_id . ' ORDER BY sort, name';
  $sql = rex_sql::factory();
  //$sql->debugsql = true;
  $sql->setQuery($query);
  
  $content = '<?php'."\n";
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $content .='$REX[\'MEDIA\'][\'RE_CAT_ID\']['. $category_id .']['. $i .'] = \''. $sql->getValue('id') .'\';'."\n";
    $sql->next();
  }
  $content .= '?>';
  
  $list_file = $REX['SRC_PATH']."/generated/files/$category_id.mclist";
  if (rex_put_file_contents($list_file, $content))
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
  
  $query = 'SELECT filename FROM ' . OOMedia :: _getTableName() . ' WHERE SUBSTRING(filename,LOCATE( ".",filename)+1) = "' . $extension . '"';
  $sql = rex_sql::factory();
  $sql->setQuery($query);
  
  $content = '<?php'."\n";
  for ($i = 0; $i < $sql->getRows(); $i++)
  {
    $content .= '$REX[\'MEDIA\'][\'EXTENSION\'][\''. $extension .'\']['. $i .'] = \''. $sql->getValue('filename') .'\';'."\n";
    $sql->next();
  }
  $content .= '?>';
  
  $list_file = $REX['SRC_PATH']."/generated/files/$extension.mextlist";
  if (rex_put_file_contents($list_file, $content))
    return true;
  
  return false;
}