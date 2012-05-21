<?php

class rex_articleMetainfoHandler extends rex_metainfoHandler
{
  const PREFIX = 'art_';

  protected function handleSave(array $params, rex_sql $sqlFields)
  {
    // Nur speichern wenn auch das MetaForm ausgefüllt wurde
    // z.b. nicht speichern wenn über be_search select navigiert wurde
    if(rex_post('meta_article_name', 'string', null) === null) return $params;

    $article = rex_sql::factory();
    // $article->debugsql = true;
    $article->setTable(rex::getTablePrefix(). 'article');
    $article->setWhere('id=:id AND clang=:clang', array('id'=> $params['id'], 'clang' => $params['clang']));

    parent::fetchRequestValues($params, $article, $sqlFields);

    // do the save only when metafields are defined
    if($article->hasValues())
      $article->update();

    // Artikel nochmal mit den zusätzlichen Werten neu generieren
    rex_article_cache::generateMeta($params['id'], $params['clang']);

    return $params;
  }

  function buildFilterCondition(array $params)
  {
    $restrictionsCondition = '';

    if(!empty($params['id']))
    {
      $s = '';
      $OOArt = rex_ooArticle::getArticleById($params['id'], $params['clang']);

      // Alle Metafelder des Pfades sind erlaubt
      foreach($OOArt->getPathAsArray() as $pathElement)
      {
        if($pathElement != '')
        {
          $s .= ' OR `p`.`restrictions` LIKE "%|'. $pathElement .'|%"';
        }
      }

      $restrictionsCondition = 'AND (`p`.`restrictions` = "" OR `p`.`restrictions` IS NULL '. $s .')';
    }

    return $restrictionsCondition;
  }

  function renderFormItem($field, $tag, $tag_attr, $id, $label, $labelIt, $typeLabel)
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

  public function extendForm(array $params)
  {
    $OOArt = rex_ooArticle::getArticleById($params['id'], $params['clang']);

    $params['activeItem'] = $params['article'];
    // Hier die category_id setzen, damit beim klick auf den REX_LINK_BUTTON der Medienpool in der aktuellen Kategorie startet
    $params['activeItem']->setValue('category_id', $OOArt->getCategoryId());

    return $params['subject'] . parent::renderFormAndSave(self::PREFIX, $params);
  }
}

$artHandler = new rex_articleMetainfoHandler();

rex_extension::register('ART_META_FORM', array($artHandler, 'extendForm'));
