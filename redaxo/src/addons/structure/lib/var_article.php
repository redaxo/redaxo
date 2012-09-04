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
    $id    = $this->getParsedArg('id', 0, true);
    $clang = $this->getParsedArg('clang', 'null');
    $ctype = $this->getParsedArg('ctype', -1);
    $field = $this->getParsedArg('field');

    $noId = $id == 0;
    if ($noId) {
      $id = '$this->getValue(\'id\')';
    }

    if ($field) {
      return __CLASS__ . '::getArticleValue(' . $id . ', ' . $field . ', ' . $clang . ')';
    } elseif (!$noId || !in_array($this->getContext(), array('module', 'action'))) {
      // aktueller Artikel darf nur in Templates, nicht in Modulen eingebunden werden
      // => endlossschleife
      if ($noId && $clang == 'null') {
        return '$this->getArticle(' . $ctype . ')';
      }
      return __CLASS__ . '::getArticle(' . $id . ', ' . $ctype . ', ' . $clang . ')';
    }

    return false;
  }

  static public function getArticleValue($id, $field, $clang = null)
  {
    if ($clang === null) {
      $clang = rex_clang::getId();
    }
    $article = rex_ooArticle::getArticleById($id, $clang);
    return htmlspecialchars($article->getValue($field));
  }

  static public function getArticle($id, $ctype = -1, $clang = null)
  {
    if ($clang === null) {
      $clang = rex_clang::getId();
    }
    $article = new rex_article($id, $clang);
    return $article->getArticle($ctype);
  }
}
