<?php

/**
 * Verschiebt einen Slice nach oben
 *
 * @param int $slice_id Id des Slices
 * @param int $clang    Id der Sprache
 *
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_moveSliceUp($slice_id, $clang)
{
  return rex_moveSlice($slice_id, $clang, 'moveup');
}

/**
 * Verschiebt einen Slice nach unten
 *
 * @param int $slice_id Id des Slices
 * @param int $clang    Id der Sprache
 *
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_moveSliceDown($slice_id, $clang)
{
  return rex_moveSlice($slice_id, $clang, 'movedown');
}

/**
 * Verschiebt einen Slice
 *
 * @param int    $slice_id  Id des Slices
 * @param int    $clang     Id der Sprache
 * @param string $direction Richtung in die verschoben werden soll
 *
 * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
 */
function rex_moveSlice($slice_id, $clang, $direction)
{
  $success = false;
  try {
    $message = rex_content_service::moveSlice($slice_id, $clang, $direction);
    $success = true;
  } catch (rex_api_exception $e)
  {
    $message = $e->getMessage();
  }
  return array($success, $message);
}

/**
 * Löscht einen Slice
 *
 * @param int    $slice_id  Id des Slices
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_deleteSlice($slice_id)
{
  return rex_content_service::deleteSlice($slice_id);
}

/**
 * Kopiert eine Kategorie in eine andere
 *
 * @param int $from_cat_id KategorieId der Kategorie, die kopiert werden soll (Quelle)
 * @param int $to_cat_id   KategorieId der Kategorie, IN die kopiert werden soll (Ziel)
 */
function rex_copyCategory($from_cat, $to_cat)
{
  return rex_category_service::copyCategory($from_cat, $to_cat);
}

/**
 * Kopiert die Metadaten eines Artikels in einen anderen Artikel
 *
 * @param int $from_id      ArtikelId des Artikels, aus dem kopiert werden (Quell ArtikelId)
 * @param int $to_id        ArtikelId des Artikel, in den kopiert werden sollen (Ziel ArtikelId)
 * @param int [$from_clang] ClangId des Artikels, aus dem kopiert werden soll (Quell ClangId)
 * @param int [$to_clang]   ClangId des Artikels, in den kopiert werden soll (Ziel ClangId)
 * @param array [$params]     Array von Spaltennamen, welche kopiert werden sollen
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_copyMeta($from_id, $to_id, $from_clang = 0, $to_clang = 0, $params = array ())
{
  return rex_article_service::copyMeta($from_id, $to_id, $from_clang, $to_clang, $params);
}

/**
 * Kopiert die Inhalte eines Artikels in einen anderen Artikel
 *
 * @param int $from_id           ArtikelId des Artikels, aus dem kopiert werden (Quell ArtikelId)
 * @param int $to_id             ArtikelId des Artikel, in den kopiert werden sollen (Ziel ArtikelId)
 * @param int [$from_clang]      ClangId des Artikels, aus dem kopiert werden soll (Quell ClangId)
 * @param int [$to_clang]        ClangId des Artikels, in den kopiert werden soll (Ziel ClangId)
 * @param int [$from_re_sliceid] Id des Slices, bei dem begonnen werden soll
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_copyContent($from_id, $to_id, $from_clang = 0, $to_clang = 0, $from_re_sliceid = 0, $revision = 0)
{
  return rex_content_service::copyContent($from_id, $to_id, $from_clang, $to_clang, $from_re_sliceid, $revision);
}

/**
 * Kopieren eines Artikels von einer Kategorie in eine andere
 *
 * @param int $id          ArtikelId des zu kopierenden Artikels
 * @param int $to_cat_id   KategorieId in die der Artikel kopiert werden soll
 *
 * @return boolean FALSE bei Fehler, sonst die Artikel Id des neue kopierten Artikels
 */
function rex_copyArticle($id, $to_cat_id)
{
  return rex_article_service::copyArticle($id, $to_cat_id);
}

/**
 * Verschieben eines Artikels von einer Kategorie in eine Andere
 *
 * @param int $id          ArtikelId des zu verschiebenden Artikels
 * @param int $from_cat_id KategorieId des Artikels, der Verschoben wird
 * @param int $to_cat_id   KategorieId in die der Artikel verschoben werden soll
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_moveArticle($id, $from_cat_id, $to_cat_id)
{
  return rex_article_service::moveArticle($id, $from_cat_id, $to_cat_id);
}

/**
 * Verschieben einer Kategorie in eine andere
 *
 * @param int $from_cat_id KategorieId der Kategorie, die verschoben werden soll (Quelle)
 * @param int $to_cat_id   KategorieId der Kategorie, IN die verschoben werden soll (Ziel)
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_moveCategory($from_cat, $to_cat)
{
  return rex_category_service::moveCategory($from_cat, $to_cat);  
}

/**
 * Generiert den Artikel-Cache des Artikelinhalts.
 *
 * @param $article_id Id des zu generierenden Artikels
 * @param [$clang ClangId des Artikels]
 *
 * @return TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
 */
function rex_generateArticleContent($article_id, $clang = null)
{
  return rex_content_service::generateArticleContent($article_id, $clang);  
}