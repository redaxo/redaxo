<?php

/**
 * Klassen zum erhalten der Rückwärtskompatibilität zu älteren REDAXO Versionen,
 * NICHT für ältere PHP Versionen!
 *
 * Dieser werden beim nächsten Versionssprung entfallen
 * @version svn:$Id$
 */

// rex_sql -> sql alias
// Für < R3.3
class sql extends rex_sql
{
	var $select;

  function sql($DBID = 1)
  {
    parent::rex_sql($DBID);
    // Altes feld wurde umbenannt, deshalb hier als Alias speichern
    $this->select =& $this->query;
  }

  function get_array($sql = "", $fetch_type = MYSQL_ASSOC)
  {
    return $this->getArray($sql, $fetch_type);
  }

  function getLastID()
  {
    return $this->getLastId();
  }

  /**
   * Setzt den Cursor des Resultsets auf die nächst höhere Stelle
   * @see #next();
   */
  function nextValue()
  {
  	$this->next();
  }

  /**
   * Setzt den Cursor des Resultsets zurück zum Anfang
   */
  function resetCounter()
  {
    $this->reset();
  }

  /**
   * Setzt die WHERE Bedienung der Abfrage
   */
  function where($where)
  {
    $this->setWhere($where);
  }

  /**
   * Sendet eine Abfrage an die Datenbank
   */
  function query($qry)
  {
    return $this->setQuery($qry);
  }
}

// rex_select -> select alias
// Für < R3.3
class select extends rex_select
{

  function select()
  {
    parent::rex_select();
  }

  ################ set multiple
  function multiple($mul)
  {
  	$this->setMultiple($mul);
  }

  ################ select extra
  function set_selectextra($extra)
  {
  	foreach(rex_var::splitString($extra) as $name => $value)
  	{
  		$this->setAttribute($name, $value);
  	}
  }

  function out()
  {
  	return $this->get();
  }

  function set_name($name)
  {
  	$this->setName($name);
  }

  function set_id($id)
  {
  	$this->setId($id);
  }

  function set_size($size)
  {
  	$this->setSize($size);
  }

  function set_selected($selected)
  {
  	$this->setSelected($selected);
  }

  function reset_selected()
  {
  	$this->resetSelected();
  }

  function set_style($style)
  {
  	$this->setStyle($style);
  }

  function add_option($name, $value, $id = 0, $re_id = 0)
  {
  	$this->addOption($name, $value, $id, $re_id);
  }
}

// rex_article -> article alias
// Für < R3.3
class article extends rex_article{

  function article($article_id = null, $clang = null)
  {
    parent::rex_article($article_id, $clang);
  }
}


// ----------------------------------------- Functions

// rex_showScripttime  -> showScripttime alias
// rex_getCurrentTime  -> getCurrentTime alias
// rex_startScripttime -> startScripttime alias
// Für < R4.2
function showScripttime()
{
  rex_showScriptTime();
}

function getCurrentTime()
{
  rex_getCurrentTime();
}

function startScripttime()
{
  rex_startScriptTime;
}

// rex_getUrl -> getUrlById alias
// Für < R3.1
function getUrlByid($id, $clang = "", $params = "")
{
  return rex_getUrl($id, $clang, $params);
}

// rex_title -> title alias
// Für < R3.2
function title($head, $subtitle = '', $styleclass = "grey", $width = '770px')
{
  return rex_title($head, $subtitle, $styleclass, $width);
}

// rex_parseArticleName -> rex_parse_article_name
// Für < R3.2
function rex_parseArticleName($name)
{
  return rex_parse_article_name($name);
}

// rex_medien* -> rex_media*
// Für < R4.2
function rex_medienpool_filename($FILENAME, $doSubindexing = true)
{
  return rex_mediapool_filename($FILENAME, $doSubindexing);  
}

function rex_medienpool_saveMedia($FILE, $rex_file_category, $FILEINFOS, $userlogin = null, $doSubindexing = TRUE)
{
  return rex_mediapool_saveMedia($FILE, $rex_file_category, $FILEINFOS, $userlogin, $doSubindexing);
}

function rex_medienpool_updateMedia($FILE, &$FILEINFOS, $userlogin = null)
{
  return rex_mediapool_updateMedia($FILE, $FILEINFOS, $userlogin);  
}

function rex_medienpool_syncFile($physical_filename,$category_id,$title,$filesize = null, $filetype = null, $doSubindexing = FALSE)
{
  return rex_mediapool_syncFile($physical_filename,$category_id,$title,$filesize, $filetype, $doSubindexing);
}

function rex_medienpool_addMediacatOptions( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  return rex_mediapool_addMediacatOptions( $select, $mediacat, $mediacat_ids, $groupName);
}

function rex_medienpool_addMediacatOptionsWPerm( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  return rex_mediapool_addMediacatOptionsWPerm( $select, $mediacat, $mediacat_ids, $groupName);
}

function rex_medienpool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
{
  return rex_mediapool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form);
}

function rex_medienpool_Uploadform($rex_file_category)
{
  return rex_mediapool_Uploadform($rex_file_category);
}

function rex_medienpool_Syncform($rex_file_category)
{
  return rex_mediapool_Syncform($rex_file_category);
}

/**
 * Fügt einen rex_select Objekt die hierarchische Medienkategorien struktur
 * hinzu
 *
 * @param $select
 * @param $mediacat
 * @param $mediacat_ids
 * @param $groupName
 * 
 * @deprecated since REDAXO 4.3
 * @see rex_mediacategory_select
 */
function rex_mediapool_addMediacatOptions( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  global $REX;

  if(empty($mediacat)) return;

  $mname = $mediacat->getName();
  if($REX['USER']->hasPerm('advancedMode[]'))
    $mname .= ' ['. $mediacat->getId() .']';

  $mediacat_ids[] = $mediacat->getId();
  $select->addOption($mname,$mediacat->getId(), $mediacat->getId(),$mediacat->getParentId());
  $childs = $mediacat->getChildren();
  if (is_array($childs))
  {
    foreach ( $childs as $child) {
      rex_mediapool_addMediacatOptions( $select, $child, $mediacat_ids, $mname);
    }
  }
}

/**
 * Fügt einen rex_select Objekt die hierarchische Medienkategorien struktur
 * hinzu unter berücksichtigung der Medienkategorierechte
 *
 * @param $select
 * @param $mediacat
 * @param $mediacat_ids
 * @param $groupName
 * 
 * @deprecated since REDAXO 4.3
 * @see rex_mediacategory_select
 */
function rex_mediapool_addMediacatOptionsWPerm( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  global $PERMALL, $REX;

  if(empty($mediacat)) return;

  $mname = $mediacat->getName();
  if($REX['USER']->hasPerm('advancedMode[]'))
    $mname .= ' ['. $mediacat->getId() .']';

  $mediacat_ids[] = $mediacat->getId();
  if ($PERMALL || $REX['USER']->hasPerm('media['.$mediacat->getId().']'))
    $select->addOption($mname,$mediacat->getId(), $mediacat->getId(),$mediacat->getParentId());

  $childs = $mediacat->getChildren();
  if (is_array($childs))
  {
    foreach ( $childs as $child) {
      rex_mediapool_addMediacatOptionsWPerm( $select, $child, $mediacat_ids, $mname);
    }
  }
}

// ----------------------------------------- Variables

// ---- since R4.2
$REX_USER =& $REX["USER"];
$REX_LOGIN = &$REX["LOGIN"];

$article_id =& $REX['ARTICLE_ID'];
$clang =& $REX['CUR_CLANG'];
