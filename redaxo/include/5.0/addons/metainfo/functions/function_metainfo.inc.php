<?php
/**
 * MetaForm Addon
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */

/**
 * Fügt einen neuen Feldtyp ein
 *
 * Gibt beim Erfolg die Id des Feldes zurück, bei Fehler die Fehlermeldung
 */
function a62_add_field_type($label, $dbtype, $dblength)
{
  global $REX, $I18N;

  if(!is_string($label) || empty($label))
    return $I18N->msg('minfo_field_error_invalid_name');

  if(!is_string($dbtype) || empty($dbtype))
    return $I18N->msg('minfo_field_error_invalid_type');

  if(!is_int($dblength) || empty($dblength))
    return $I18N->msg('minfo_field_error_invalid_length');

  $qry = 'SELECT * FROM '. $REX['TABLE_PREFIX']. '62_type WHERE label="'. addslashes($label) .'" LIMIT 1';
  $sql = rex_sql::factory();
  $sql->setQuery($qry);
  if($sql->getRows() != 0)
    return $I18N->msg('minfo_field_error_unique_type');

  $sql->setTable($REX['TABLE_PREFIX']. '62_type');
  $sql->setValue('label', $label);
  $sql->setValue('dbtype', $dbtype);
  $sql->setValue('dblength', $dblength);

  if($sql->insert())
  {
    return $sql->getLastId();
  }
  return $sql->getError();
}

/**
 * Löscht einen Feldtyp
 *
 * Gibt beim Erfolg true zurück, sonst eine Fehlermeldung
 */
function a62_delete_field_type($field_type_id)
{
  global $REX;

  if(!is_int($field_type_id) || empty($field_type_id))
    return $I18N->msg('minfo_field_error_invalid_typeid');

  $sql = rex_sql::factory();
  $sql->setTable($REX['TABLE_PREFIX']. '62_type');
  $sql->setWhere('id='. $field_type_id);

  if(!$sql->delete())
    return $sql->getError();

  return $sql->getRows() == 1;
}

/**
 * Fügt ein MetaFeld hinzu und legt dafür eine Spalte in der MetaTable an
 */
function a62_add_field($title, $name, $prior, $attributes, $type, $default, $params = null, $validate = null, $restrictions = '')
{
  global $REX, $I18N;

  $prefix = a62_meta_prefix($name);
  $metaTable = a62_meta_table($prefix);

  // Prefix korrekt?
  if(!$metaTable)
    return $I18N->msg('minfo_field_error_invalid_prefix');

  // TypeId korrekt?
  $qry = 'SELECT * FROM '. $REX['TABLE_PREFIX'] .'62_type WHERE id='. $type .' LIMIT 2';
  $sql = rex_sql::factory();
  $typeInfos = $sql->getArray($qry);

  if($sql->getRows() != 1)
    return $I18N->msg('minfo_field_error_invalid_type');

  $fieldDbType = $typeInfos[0]['dbtype'];
  $fieldDbLength = $typeInfos[0]['dblength'];

  // Spalte existiert schon?
  $sql->setQuery('SELECT * FROM '. $metaTable . ' LIMIT 1');
  if(in_array($name, $sql->getFieldnames()))
    return $I18N->msg('minfo_field_error_unique_name');

  // Spalte extiert laut a62_params?
  $sql->setQuery('SELECT * FROM '. $REX['TABLE_PREFIX']. '62_params WHERE name="'. addslashes($name) .'" LIMIT 1');
  if($sql->getRows() != 0)
    return $I18N->msg('minfo_field_error_unique_name');

  $sql->setTable($REX['TABLE_PREFIX']. '62_params');
  $sql->setValue('title', $title);
  $sql->setValue('name', $name);
  $sql->setValue('prior', $prior);
  $sql->setValue('attributes', $attributes);
  $sql->setValue('type', $type);
  $sql->setValue('default', $default);
  $sql->setValue('params', $params);
  $sql->setValue('validate', $validate);
  $sql->setValue('restrictions', $restrictions);
  $sql->addGlobalUpdateFields();
  $sql->addGlobalCreateFields();

  if(!$sql->insert())
    return $sql->getError();

  rex_organize_priorities($REX['TABLE_PREFIX']. '62_params', 'prior', 'name LIKE "'. $prefix .'%"', 'prior, updatedate', 'field_id');

  $tableManager = new rex_a62_tableManager($metaTable);
  return $tableManager->addColumn($name, $fieldDbType, $fieldDbLength, $default);
}

function a62_delete_field($fieldIdOrName)
{
  global $REX, $I18N;

  // Löschen anhand der FieldId
  if(is_int($fieldIdOrName))
  {
    $fieldQry = 'SELECT * FROM '. $REX['TABLE_PREFIX']. '62_params WHERE field_id='. $fieldIdOrName .' LIMIT 2';
    $invalidField = $I18N->msg('minfo_field_error_invalid_fieldid');
  }
  // Löschen anhand des Feldnames
  else if(is_string($fieldIdOrName))
  {
    $fieldQry = 'SELECT * FROM '. $REX['TABLE_PREFIX']. '62_params WHERE name="'. addslashes($fieldIdOrName) .'" LIMIT 2';
    $invalidField = $I18N->msg('minfo_field_error_invalid_name');
  }
  else
  {
    trigger_error('MetaInfos: Unexpected type for $fieldIdOrName!', E_USER_ERROR);
  }
  // Feld existiert?
  $sql = rex_sql::factory();
  $fieldInfos = $sql->getArray($fieldQry);

  if($sql->getRows() != 1)
    return $invalidField;

  $name = $fieldInfos[0]['name'];
  $field_id = $fieldInfos[0]['field_id'];

  $prefix = a62_meta_prefix($name);
  $metaTable = a62_meta_table($prefix);

  // Spalte existiert?
  $sql->setQuery('SELECT * FROM '. $metaTable . ' LIMIT 1');
  if(!in_array($name, $sql->getFieldnames()))
    return $I18N->msg('minfo_field_error_invalid_name');

  $sql->setTable($REX['TABLE_PREFIX']. '62_params');
  $sql->setWhere('field_id='. $field_id);

  if(!$sql->delete())
    return $sql->getError();

  $tableManager = new rex_a62_tableManager($metaTable);
  return $tableManager->deleteColumn($name);
}

/**
 * Extrahiert den Prefix aus dem Namen eine Spalte
 */
function a62_meta_prefix($name)
{
  if(!is_string($name)) return false;

  if(($pos = strpos($name, '_')) !== false)
    return substr(strtolower($name), 0, $pos+1);

  return false;
}

/**
 * Gibt die mit dem Prefix verbundenen Tabellennamen zurück
 */
function a62_meta_table($prefix)
{
  $metaTables = OOAddon::getProperty('metainfo', 'metaTables', array());

  if(isset($metaTables[$prefix]))
    return $metaTables[$prefix];

  return false;
}

/**
 * Bindet ggf extensions ein
 * 
 * @param $params
 */
function a62_extensions_handler($params)
{
  global $REX;
  
  $page = $params['subject'];
  $mode = rex_request('mode', 'string');
  $mypage = 'metainfo';
  
  // additional javascripts
  if($page == 'metainfo' || ($page == 'content' && $mode == 'meta'))
  {
    rex_register_extension('PAGE_HEADER',
      create_function('$params', 'return $params[\'subject\'] ."\n".\'  <script src="../files/addons/'. $mypage .'/metainfo.js" type="text/javascript"></script>\';')
    );
  }
  
  // include extensions
  if ($page == 'content' && $mode == 'meta')
  {
    require_once ($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/extensions/extension_art_metainfo.inc.php');
  }
  elseif ($page == 'structure')
  {
    require_once ($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/extensions/extension_cat_metainfo.inc.php');
  }
  elseif ($page == 'mediapool')
  {
    require_once ($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/extensions/extension_med_metainfo.inc.php');
  }
  elseif ($page == 'import_export')
  {
    require_once ($REX['INCLUDE_PATH'] . '/addons/' . $mypage . '/extensions/extension_cleanup.inc.php');
  }
}
