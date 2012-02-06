<?php

/**
 * REX_ARTICLE[1]
 * REX_ARTICLE[id=1]
 *
 * REX_ARTICLE[id=1 ctype=2 clang=1]
 *
 * REX_ARTICLE[field='id']
 * REX_ARTICLE[field='description' id=3]
 * REX_ARTICLE[field='description' id=3 clang=2]
 *
 * Attribute:
 *   - clang     => ClangId des Artikels festlegen
 *   - ctype     => Spalte des Artikels festlegen
 *   - field     => Nur dieses Feld des Artikels ausgeben
 *
 * @package redaxo5
 */

class rex_var_article extends rex_var
{
  /**
   * Werte für die Ausgabe
   */
  protected function getOutput()
  {
    $article_id = (int) $this->getArg('id', 0, true);
    $clang = $this->hasArg('clang') ? (int) $this->getArg('clang') : 'null';
    $ctype = (int) $this->getArg('ctype', -1);
    $field = (string) $this->getArg('field', '');

    if($article_id > 0)
    {
      $article = $article_id;
    }
    elseif($clang == 'null')
    {
      $article = '$this->getValue(\'id\')';
    }
    else
    {
      $article = '$this';
    }

    if($field)
    {
      if(rex_ooArticle::hasValue($field))
      {
        return __CLASS__ .'::getArticleValue('. $article .", '". $field ."', ". $clang .')';
      }
    }
    else
    {
      if($this->getContext() != 'module')
      {
        // aktueller Artikel darf nur in Templates, nicht in Modulen eingebunden werden
        // => endlossschleife
        return __CLASS__ .'::getArticle('. $article .', '. $ctype .', '. $clang .')';
      }
    }

    return false;
  }

  static public function getArticleValue($article, $field, $clang = null)
  {
    if($clang === null)
    {
      $clang = rex_clang::getId();
    }
    if(!is_object($article))
    {
      $article = rex_ooArticle::getArticleById($article, $clang);
    }

    $value = $article->getValue($field);
    return htmlspecialchars($value);
  }

  static public function getArticle($article, $ctype = -1, $clang = null)
  {
    if($clang === null)
    {
      $clang = rex_clang::getId();
    }
    if(!is_object($article))
    {
      $article = new rex_article($article, $clang);
    }

    $article = $article->getArticle($ctype);
    return $article;
  }
}