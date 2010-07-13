<?php

/**
 * MetaForm Addon
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * 
 * @package redaxo4
 * @version svn:$Id$
 */

rex_register_extension('ART_META_FORM', 'rex_a62_metainfo_form');

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
  $OOArt = OOArticle::getArticleById($params['id'], $params['clang']);

  $params['activeItem'] = $params['article'];
  // Hier die category_id setzen, damit beim klick auf den REX_LINK_BUTTON der Medienpool in der aktuellen Kategorie startet
  $params['activeItem']->setValue('category_id', $OOArt->getCategoryId());

  return $params['subject'] . _rex_a62_metainfo_form('art_', $params, '_rex_a62_metainfo_art_handleSave');
}