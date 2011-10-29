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
  return rex_content_service::copyCategory($from_cat, $to_cat);
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
  return rex_content_service::copyMeta($from_id, $to_id, $from_clang, $to_clang, $params);
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
  return rex_content_service::copyArticle($id, $to_cat_id);
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
  return rex_content_service::moveArticle($id, $from_cat_id, $to_cat_id);
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
  return rex_content_service::moveCategory($from_cat, $to_cat);  
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
  return rex_content_service::moveCategory($from_cat, $to_cat);  
}

/**
* Führt alle pre-view Aktionen eines Moduls aus
*
* @param int    $module_id  Id des Moduls
* @param string $function   Funktion/Modus der Aktion
* @param array  $REX_ACTION Array zum modifizieren der initialwerte
*
* @return array Das gefüllte REX_ACTION-Array
*/
function rex_execPreViewAction($module_id, $function, $REX_ACTION)
{
  $modebit = rex_getActionModeBit($function);

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, preview FROM '.rex::getTablePrefix().'module_action ma,'. rex::getTablePrefix(). 'action a WHERE preview != "" AND ma.action_id=a.id AND module_id='. $module_id .' AND ((a.previewmode & '. $modebit .') = '. $modebit .')');

  foreach ($ga as $row)
  {
    $iaction = $row->getValue('preview');

    // ****************** VARIABLEN ERSETZEN
    foreach(rex_var::getVars() as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_stream::factory('action/'. $row->getValue('id') .'/preview', $iaction);
  }

  return $REX_ACTION;
}

/**
 * Führt alle pre-save Aktionen eines Moduls aus
 *
 * @param int    $module_id  Id des Moduls
 * @param string $function   Funktion/Modus der Aktion
 * @param array  $REX_ACTION Array zum speichern des Status
 *
 * @return array Ein Array welches eine Meldung sowie das gefüllte REX_ACTION-Array beinhaltet
 */
function rex_execPreSaveAction($module_id, $function, $REX_ACTION)
{
  $modebit = rex_getActionModeBit($function);
  $messages = array();

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, presave FROM ' . rex::getTablePrefix() . 'module_action ma,' . rex::getTablePrefix() . 'action a WHERE presave != "" AND ma.action_id=a.id AND module_id=' . $module_id . ' AND ((a.presavemode & ' . $modebit . ') = ' . $modebit . ')');

  foreach($ga as $row)
  {
    $REX_ACTION['MSG'] = '';
    $iaction = $row->getValue('presave');

    // *********************** WERTE ERSETZEN
    foreach (rex_var::getVars() as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_stream::factory('action/'. $row->getValue('id') .'/presave', $iaction);

    if ($REX_ACTION['MSG'] != '')
    $messages[] = $REX_ACTION['MSG'];
  }
  return array(implode(' | ', $messages), $REX_ACTION);
}

/**
 * Führt alle post-save Aktionen eines Moduls aus
 *
 * @param int    $module_id  Id des Moduls
 * @param string $function   Funktion/Modus der Aktion
 * @param array  $REX_ACTION Array zum speichern des Status
 *
 * @return string Eine Meldung
 */
function rex_execPostSaveAction($module_id, $function, $REX_ACTION)
{
  $modebit = rex_getActionModeBit($function);
  $messages = array();

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, postsave FROM ' . rex::getTablePrefix() . 'module_action ma,' . rex::getTablePrefix() . 'action a WHERE postsave != "" AND ma.action_id=a.id AND module_id=' . $module_id . ' AND ((a.postsavemode & ' . $modebit . ') = ' . $modebit . ')');

  foreach ($ga as $row)
  {
    $REX_ACTION['MSG'] = '';
    $iaction = $row->getValue('postsave');

    // ***************** WERTE ERSETZEN UND POSTACTION AUSFÜHREN
    foreach (rex_var::getVars() as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_stream::factory('action/'. $row->getValue('id') .'/postsave', $iaction);

    if ($REX_ACTION['MSG'] != '')
    $messages[] = $REX_ACTION['MSG'];
  }
  return implode(' | ', $messages);
}

/**
 * Übersetzt den Modus in das dazugehörige Bitwort
 *
 * @param string $function   Funktion/Modus der Aktion
 *
 * @return int Ein Bitwort
 */
function rex_getActionModeBit($function)
{
  if ($function == 'edit')
  $modebit = '2'; // pre-action and edit
  elseif ($function == 'delete')
  $modebit = '4'; // pre-action and delete
  else
  $modebit = '1'; // pre-action and add

  return $modebit;
}