<?php

/**
 * Klassen zum erhalten der Rückwärtskompatibilität zu älteren REDAXO Versionen,
 * NICHT für ältere PHP Versionen!
 *
 * Dieser werden beim nächsten Versionssprung entfallen
 * @version svn:$Id$
 */

// ----------------------------------------- Functions

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
