<?php

class rex_articleMetainfoHandler extends rex_metainfoHandler
{
  function _rex_a62_metainfo_art_handleSave($params, $sqlFields)
  {
  	// Nur speichern wenn auch das MetaForm ausgefüllt wurde
  	// z.b. nicht speichern wenn über be_search select navigiert wurde
    if(rex_post('meta_article_name', 'string', null) === null) return $params;
  
    return rex_categoryMetainfoHandler::_rex_a62_metainfo_cat_handleSave($params, $sqlFields);
  }
  
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
    $OOArt = rex_ooArticle::getArticleById($params['id'], $params['clang']);
  
    $params['activeItem'] = $params['article'];
    // Hier die category_id setzen, damit beim klick auf den REX_LINK_BUTTON der Medienpool in der aktuellen Kategorie startet
    $params['activeItem']->setValue('category_id', $OOArt->getCategoryId());
  
    return $params['subject'] . parent::_rex_a62_metainfo_form('art_', $params, array($this, '_rex_a62_metainfo_art_handleSave'));
  }  
}

$artHandler = new rex_articleMetainfoHandler();

rex_extension::register('ART_META_FORM', array($artHandler, 'rex_a62_metainfo_form'));
