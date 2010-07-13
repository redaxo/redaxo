<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

rex_register_extension('MEDIA_FORM_EDIT', 'rex_a62_metainfo_form');
rex_register_extension('MEDIA_FORM_ADD', 'rex_a62_metainfo_form');

rex_register_extension('MEDIA_ADDED', 'rex_a62_metainfo_form');
rex_register_extension('MEDIA_UPDATED', 'rex_a62_metainfo_form');

/**
 * Callback, dass ein Formular item formatiert
 */
function rex_a62_metainfo_form_item($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
{
  $s = '';
  
  if($typeLabel != 'legend')
    $s .= '<div class="rex-form-row">';

  if($tag != '')
    $s .= '<'. $tag . $tag_attr  .'>'. "\n";

  if($labelIt)
    $s .= '<label for="'. $id .'">'. $label .'</label>'. "\n";

  $s .= $field. "\n";

  if($tag != '')
    $s .='</'.$tag.'>'. "\n";
	
  if($typeLabel != 'legend')
    $s .= '</div>';

  return $s;
}

/**
 * Erweitert das Meta-Formular um die neuen Meta-Felder
 */
function rex_a62_metainfo_form($params)
{
  // Nur beim EDIT gibts auch ein Medium zum bearbeiten
  if($params['extension_point'] == 'MEDIA_FORM_EDIT')
  {
    $params['activeItem'] = $params['media'];
    unset($params['media']);
    // Hier die category_id setzen, damit keine Warnung entsteht (REX_LINK_BUTTON)
    // $params['activeItem']->setValue('category_id', 0);
  }
  else if($params['extension_point'] == 'MEDIA_ADDED')
  {
    global $REX;

    $sql = rex_sql::factory();
    $qry = 'SELECT file_id FROM '. $REX['TABLE_PREFIX'] .'file WHERE filename="'. $params['filename'] .'"';
    $sql->setQuery($qry);
    if($sql->getRows() == 1)
    {
      $params['file_id'] = $sql->getValue('file_id');
    }
    else
    {
      trigger_error('Error occured during file upload!', E_USER_ERROR);
      exit();
    }
  }

  return _rex_a62_metainfo_form('med_', $params, '_rex_a62_metainfo_med_handleSave');
}