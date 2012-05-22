<?php

/**
 * @see rex_mediapool_filename()
 *
 * @deprecated 4.2
 */
function rex_medienpool_filename($FILENAME, $doSubindexing = true)
{
  return rex_mediapool_filename($FILENAME, $doSubindexing);
}

/**
 * @see rex_mediapool_saveMedia()
 *
 * @deprecated 4.2
 */
function rex_medienpool_saveMedia($FILE, $rex_file_category, $FILEINFOS, $userlogin = null, $doSubindexing = TRUE)
{
  return rex_mediapool_saveMedia($FILE, $rex_file_category, $FILEINFOS, $userlogin, $doSubindexing);
}

/**
 * @see rex_mediapool_updateMedia()
 *
 * @deprecated 4.2
 */
function rex_medienpool_updateMedia($FILE, &$FILEINFOS, $userlogin = null)
{
  return rex_mediapool_updateMedia($FILE, $FILEINFOS, $userlogin);
}

/**
 * @see rex_mediapool_syncFile()
 *
 * @deprecated 4.2
 */
function rex_medienpool_syncFile($physical_filename,$category_id,$title,$filesize = null, $filetype = null, $doSubindexing = FALSE)
{
  return rex_mediapool_syncFile($physical_filename,$category_id,$title,$filesize, $filetype, $doSubindexing);
}

/**
 * @see rex_mediapool_addMediacatOptions()
 *
 * @deprecated 4.2
 */
function rex_medienpool_addMediacatOptions( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  return rex_mediapool_addMediacatOptions( $select, $mediacat, $mediacat_ids, $groupName);
}

/**
 * @see rex_mediapool_addMediacatOptionsWPerm()
 *
 * @deprecated 4.2
 */
function rex_medienpool_addMediacatOptionsWPerm( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  return rex_mediapool_addMediacatOptionsWPerm( $select, $mediacat, $mediacat_ids, $groupName);
}

/**
 * @see rex_mediapool_Mediaform()
 *
 * @deprecated 4.2
 */
function rex_medienpool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form)
{
  return rex_mediapool_Mediaform($form_title, $button_title, $rex_file_category, $file_chooser, $close_form);
}

/**
 * @see rex_mediapool_Uploadform()
 *
 * @deprecated 4.2
 */
function rex_medienpool_Uploadform($rex_file_category)
{
  return rex_mediapool_Uploadform($rex_file_category);
}

/**
 * @see rex_mediapool_Syncform()
 *
 * @deprecated 4.2
 */
function rex_medienpool_Syncform($rex_file_category)
{
  return rex_mediapool_Syncform($rex_file_category);
}

/**
 * @see rex_mediacategory_select
 *
 * @deprecated 4.3
 */
function rex_mediapool_addMediacatOptions( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  global $REX;

  if(empty($mediacat)) return;

  $mname = $mediacat->getName();
  if(rex::getUser()->hasPerm('advancedMode[]'))
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
 * @see rex_mediacategory_select
 *
 * @deprecated 4.3
 */
function rex_mediapool_addMediacatOptionsWPerm( &$select, &$mediacat, &$mediacat_ids, $groupName = '')
{
  global $PERMALL, $REX;

  if(empty($mediacat)) return;

  $mname = $mediacat->getName();
  if(rex::getUser()->hasPerm('advancedMode[]'))
    $mname .= ' ['. $mediacat->getId() .']';

  $mediacat_ids[] = $mediacat->getId();
  if ($PERMALL || rex::getUser()->getComplexPerm('media')->hasCategoryPerm($mediacat->getId()))
    $select->addOption($mname,$mediacat->getId(), $mediacat->getId(),$mediacat->getParentId());

  $childs = $mediacat->getChildren();
  if (is_array($childs))
  {
    foreach ( $childs as $child) {
      rex_mediapool_addMediacatOptionsWPerm( $select, $child, $mediacat_ids, $mname);
    }
  }
}
