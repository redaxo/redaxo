<?php

require_once rex_path::addon('structure', 'functions/function_rex_structure.inc.php');


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
  global $REX;

  // ctype beachten
  // verschieben / vertauschen
  // article regenerieren.

  $success = false;
  $message = $REX['I18N']->msg('slice_moved_error');

  // check if slice id is valid
  $CM = rex_sql::factory();
  $CM->setQuery("select * from " . $REX['TABLE_PREFIX'] . "article_slice where id='$slice_id' and clang=$clang");
  if ($CM->getRows() == 1)
  {
    // prepare sql for later saving
    $upd = rex_sql::factory();
    $upd->setTable($REX['TABLE_PREFIX'] . "article_slice");
    $upd->setWhere(array(
      'id' => $slice_id
    ));
    
    // some vars for later use
    $article_id = $CM->getValue('article_id');
    $ctype = $CM->getValue('ctype');
    $slice_revision = $CM->getValue('revision');
    
    if ($direction == "moveup" || $direction == "movedown")
    {
      if ($direction == "moveup")
      {
        $upd->setValue('prior', $CM->getValue('prior')-1);
        $updSort = 'DESC';
      }
      else if ($direction == "movedown")
      {
        $upd->setValue('prior', $CM->getValue('prior')+1);
        $updSort = 'ASC';
      }
      $upd->addGlobalUpdateFields();
      $upd->update();
      
      rex_organize_priorities(
        $REX['TABLE_PREFIX'] . 'article_slice',
        'prior',
        'article_id=' . $article_id . ' AND clang=' . $clang .' AND ctype='. $ctype .' AND revision='. $slice_revision,
        'prior, updatedate '. $updSort
      );
      
      rex_deleteCacheArticleContent($article_id, $clang);
      
      $message = $REX['I18N']->msg('slice_moved');
      $success = true;
    }
    else
    {
      trigger_error('rex_moveSlice: Unsupported direction "'. $direction .'"!', E_USER_ERROR);
    }
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
  global $REX;

  // check if slice id is valid
  $curr = rex_sql::factory();
  $curr->setQuery('SELECT * FROM ' . $REX['TABLE_PREFIX'] . 'article_slice WHERE id=' . $slice_id);
  if($curr->getRows() != 1)
  {
    return false;
  }

  // delete the slice
  $del = rex_sql::factory();
  $del->setQuery('DELETE FROM ' . $REX['TABLE_PREFIX'] . 'article_slice WHERE id=' . $slice_id);
  
  // reorg remaining slices
  rex_organize_priorities(
    $REX['TABLE_PREFIX'] . 'article_slice',
    'prior',
    'article_id=' . $curr->getValue('article_id') . ' AND clang=' . $curr->getValue('clang') .' AND ctype='. $curr->getValue('ctype') .' AND revision='. $curr->getValue('revision'),
    'prior'
  );

  // check if delete was successfull
  return $curr->getRows() == 1;
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
  global $REX;
  $modebit = rex_getActionModeBit($function);

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, preview FROM '.$REX['TABLE_PREFIX'].'module_action ma,'. $REX['TABLE_PREFIX']. 'action a WHERE preview != "" AND ma.action_id=a.id AND module_id='. $module_id .' AND ((a.previewmode & '. $modebit .') = '. $modebit .')');

  while ($ga->hasNext())
  {
    $iaction = $ga->getValue('preview');

    // ****************** VARIABLEN ERSETZEN
    foreach($REX['VARIABLES'] as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_variableStream::factory('action/'. $ga->getValue('id') .'/preview', $iaction);

    $ga->next();
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
  global $REX;
  $modebit = rex_getActionModeBit($function);
	$messages = array();

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, presave FROM ' . $REX['TABLE_PREFIX'] . 'module_action ma,' . $REX['TABLE_PREFIX'] . 'action a WHERE presave != "" AND ma.action_id=a.id AND module_id=' . $module_id . ' AND ((a.presavemode & ' . $modebit . ') = ' . $modebit . ')');

  while ($ga->hasNext())
  {
    $REX_ACTION['MSG'] = '';
    $iaction = $ga->getValue('presave');

    // *********************** WERTE ERSETZEN
    foreach ($REX['VARIABLES'] as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_variableStream::factory('action/'. $ga->getValue('id') .'/presave', $iaction);

    if ($REX_ACTION['MSG'] != '')
      $messages[] = $REX_ACTION['MSG'];

    $ga->next();
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
  global $REX;
  $modebit = rex_getActionModeBit($function);
	$messages = array();

  $ga = rex_sql::factory();
  $ga->setQuery('SELECT a.id, postsave FROM ' . $REX['TABLE_PREFIX'] . 'module_action ma,' . $REX['TABLE_PREFIX'] . 'action a WHERE postsave != "" AND ma.action_id=a.id AND module_id=' . $module_id . ' AND ((a.postsavemode & ' . $modebit . ') = ' . $modebit . ')');

  while ($ga->hasNext())
  {
    $REX_ACTION['MSG'] = '';
    $iaction = $ga->getValue('postsave');

    // ***************** WERTE ERSETZEN UND POSTACTION AUSFÜHREN
    foreach ($REX['VARIABLES'] as $obj)
    {
      $iaction = $obj->getACOutput($REX_ACTION, $iaction);
    }

    require rex_variableStream::factory('action/'. $ga->getValue('id') .'/postsave', $iaction);

    if ($REX_ACTION['MSG'] != '')
      $messages[] = $REX_ACTION['MSG'];

    $ga->next();
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

/**
 * Konvertiert einen Artikel zum Startartikel der eigenen Kategorie
 *
 * @param int $neu_id  Artikel ID des Artikels, der Startartikel werden soll
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_article2startpage($neu_id){

  global $REX;

  $GAID = array();

  // neuen startartikel holen und schauen ob da
  $neu = rex_sql::factory();
  $neu->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id=$neu_id and startpage=0 and clang=0");
  if ($neu->getRows()!=1) return false;
  $neu_path = $neu->getValue("path");
  $neu_cat_id = $neu->getValue("re_id");

  // in oberster kategorie dann return
  if ($neu_cat_id == 0) return false;

  // alten startartikel
  $alt = rex_sql::factory();
  $alt->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id=$neu_cat_id and startpage=1 and clang=0");
  if ($alt->getRows()!=1) return false;
  $alt_path = $alt->getValue('path');
  $alt_id = $alt->getValue('id');
  $parent_id = $alt->getValue('re_id');

  // cat felder sammeln. +
  $params = array('path','prior','catname','startpage','catprior','status');
  $db_fields = rex_ooRedaxo::getClassVars();
  foreach($db_fields as $field)
  {
    if(substr($field,0,4)=='cat_') $params[] = $field;
  }

  // LANG SCHLEIFE
  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    // alter startartikel
    $alt->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id=$neu_cat_id and startpage=1 and clang=$clang");

    // neuer startartikel
    $neu->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id=$neu_id and startpage=0 and clang=$clang");

    // alter startartikel updaten
    $alt2 = rex_sql::factory();
    $alt2->setTable($REX['TABLE_PREFIX']."article");
    $alt2->setWhere("id=$alt_id and clang=". $clang);
    $alt2->setValue("re_id",$neu_id);

    // neuer startartikel updaten
    $neu2 = rex_sql::factory();
    $neu2->setTable($REX['TABLE_PREFIX']."article");
    $neu2->setWhere("id=$neu_id and clang=". $clang);
    $neu2->setValue("re_id",$alt->getValue("re_id"));

    // austauschen der definierten paramater
    foreach($params as $param)
    {
      $alt2->setValue($param,$neu->getValue($param));
      $neu2->setValue($param,$alt->getValue($param));
    }
    $alt2->update();
    $neu2->update();
  }

  // alle artikel suchen nach |art_id| und pfade ersetzen
  // alles artikel mit re_id alt_id suchen und ersetzen

  $articles = rex_sql::factory();
  $ia = rex_sql::factory();
  $articles->setQuery("select * from ".$REX['TABLE_PREFIX']."article where path like '%|$alt_id|%'");
  for($i=0;$i<$articles->getRows();$i++)
  {
    $iid = $articles->getValue("id");
    $ipath = str_replace("|$alt_id|","|$neu_id|",$articles->getValue("path"));

    $ia->setTable($REX['TABLE_PREFIX']."article");
    $ia->setWhere('id='.$iid);
    $ia->setValue("path",$ipath);
    if ($articles->getValue("re_id")==$alt_id) $ia->setValue("re_id",$neu_id);
    $ia->update();
    $GAID[$iid] = $iid;
    $articles->next();
  }

  $GAID[$neu_id] = $neu_id;
  $GAID[$alt_id] = $alt_id;
  $GAID[$parent_id] = $parent_id;

  foreach($GAID as $gid)
  {
    rex_deleteCacheArticle($gid);
  }

  $users = rex_sql::factory();
  $users->setQuery('UPDATE '. $REX['TABLE_PREFIX'] .'user SET rights = REPLACE(rights, "#csw['. $alt_id .']#", "#csw['. $neu_id .']#")');

  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    rex_register_extension_point('ART_TO_STARTPAGE', '', array (
      'id' => $neu_id,
      'id_old' => $alt_id,
      'clang' => $clang,
    ));
  }

  return true;
}

/**
 * Konvertiert einen Artikel in eine Kategorie
 *
 * @param int $art_id  Artikel ID des Artikels, der in eine Kategorie umgewandelt werden soll
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_article2category($art_id){

  global $REX;

  $sql = rex_sql::factory();

  // LANG SCHLEIFE
  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    // artikel
    $sql->setQuery('select re_id, name from '.$REX['TABLE_PREFIX']."article where id=$art_id and startpage=0 and clang=$clang");

    if (!isset($re_id))
      $re_id = $sql->getValue('re_id');

    // artikel updaten
    $sql->setTable($REX['TABLE_PREFIX']."article");
    $sql->setWhere("id=$art_id and clang=". $clang);
    $sql->setValue('startpage', 1);
    $sql->setValue('catname', $sql->getValue('name'));
    $sql->setValue('catprior', 100);
    $sql->update();

    rex_newCatPrio($re_id, $clang, 0, 100);
  }

  rex_deleteCacheArticleLists($re_id);
  rex_deleteCacheArticle($art_id);

  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    rex_register_extension_point('ART_TO_CAT', '', array (
      'id' => $art_id,
      'clang' => $clang,
    ));
  }

  return true;
}

/**
 * Konvertiert eine Kategorie in einen Artikel
 *
 * @param int $art_id  Artikel ID der Kategorie, die in einen Artikel umgewandelt werden soll
 *
 * @return boolean TRUE bei Erfolg, sonst FALSE
 */
function rex_category2article($art_id){

  global $REX;

  $sql = rex_sql::factory();

  // Kategorie muss leer sein
  $sql->setQuery('SELECT pid FROM '. $REX['TABLE_PREFIX'] .'article WHERE re_id='. $art_id .' LIMIT 1');
  if ($sql->getRows() != 0)
    return false;

  // LANG SCHLEIFE
  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    // artikel
    $sql->setQuery('select re_id, name from '.$REX['TABLE_PREFIX']."article where id=$art_id and startpage=1 and clang=$clang");

    if (!isset($re_id))
      $re_id = $sql->getValue('re_id');

    // artikel updaten
    $sql->setTable($REX['TABLE_PREFIX']."article");
    $sql->setWhere("id=$art_id and clang=". $clang);
    $sql->setValue('startpage', 0);
    $sql->setValue('prior', 100);
    $sql->update();

    rex_newArtPrio($re_id, $clang, 0, 100);
  }

  rex_deleteCacheArticleLists($re_id);
  rex_deleteCacheArticle($art_id);

  foreach($REX['CLANG'] as $clang => $clang_name)
  {
    rex_register_extension_point('CAT_TO_ART', '', array (
      'id' => $art_id,
      'clang' => $clang,
    ));
  }

  return true;
}

/**
 * Kopiert eine Kategorie in eine andere
 *
 * @param int $from_cat_id KategorieId der Kategorie, die kopiert werden soll (Quelle)
 * @param int $to_cat_id   KategorieId der Kategorie, IN die kopiert werden soll (Ziel)
 */
function rex_copyCategory($from_cat, $to_cat)
{
  // TODO rex_copyCategory implementieren
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
  global $REX;

  $from_clang = (int) $from_clang;
  $to_clang = (int) $to_clang;
  $from_id = (int) $from_id;
  $to_id = (int) $to_id;
  if (!is_array($params))
    $params = array ();

  if ($from_id == $to_id && $from_clang == $to_clang)
    return false;

  $gc = rex_sql::factory();
  $gc->setQuery("select * from ".$REX['TABLE_PREFIX']."article where clang='$from_clang' and id='$from_id'");

  if ($gc->getRows() == 1)
  {
    $uc = rex_sql::factory();
    // $uc->debugsql = 1;
    $uc->setTable($REX['TABLE_PREFIX']."article");
    $uc->setWhere("clang='$to_clang' and id='$to_id'");
    $uc->addGlobalUpdateFields();

    foreach ($params as $key => $value)
    {
      $uc->setValue($value, $gc->getValue($value));
    }

    $uc->update();

    rex_deleteCacheArticleMeta($to_id,$to_clang);
    return true;
  }
  return false;

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
  global $REX;

  if ($from_id == $to_id && $from_clang == $to_clang)
    return false;

  $gc = rex_sql::factory();
  $gc->setQuery("select * from ".$REX['TABLE_PREFIX']."article_slice where article_id='$from_id' and clang='$from_clang' and revision='$revision'");

  if ($gc->getRows() > 0)
  {
    $ins = rex_sql::factory();
    $ins->setTable($REX['TABLE_PREFIX']."article_slice");
    $ctypes = array();

    $cols = rex_sql::factory();
    // $cols->debugsql = 1;
    $cols->setquery("SHOW COLUMNS FROM ".$REX['TABLE_PREFIX']."article_slice");
    while($gc->hasNext())
    {
      while($cols->hasNext())
      {
        $colname = $cols->getValue("Field");
        if ($colname == "clang") $value = $to_clang;
        elseif ($colname == "article_id") $value = $to_id;
        else
          $value = $gc->getValue($colname);
  
        // collect all affected ctypes
        if ($colname == "ctype")
          $ctypes[$value] = $value;
        
        if ($colname != "id")
          $ins->setValue($colname, $value);
          
        $cols->next();
      }
      $cols->reset();
      
      $ins->addGlobalUpdateFields();
      $ins->addGlobalCreateFields();
      $ins->insert();
      
      $gc->next();
    }

    foreach($ctypes as $ctype)
    {
      // reorg slices
      rex_organize_priorities(
        $REX['TABLE_PREFIX'] . 'article_slice',
        'prior',
        'article_id=' . $to_id . ' AND clang=' . $to_clang .' AND ctype='. $ctype .' AND revision='. $revision,
        'prior, updatedate'
      );
    }
  
    rex_deleteCacheArticleContent($to_id, $to_clang);
    return true;
  }

  return false;
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
  global $REX;

  $id = (int) $id;
  $to_cat_id = (int) $to_cat_id;
  $new_id = '';

  // Artikel in jeder Sprache kopieren
  foreach ($REX['CLANG'] as $clang => $clang_name)
  {
    // validierung der id & from_cat_id
    $from_sql = rex_sql::factory();
    $qry = 'select * from '.$REX['TABLE_PREFIX'].'article where clang="'.$clang.'" and id="'. $id .'"';
    $from_sql->setQuery($qry);

    if ($from_sql->getRows() == 1)
    {
      // validierung der to_cat_id
      $to_sql = rex_sql::factory();
      $to_sql->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where clang="'.$clang.'" and startpage=1 and id="'. $to_cat_id .'"');

      if ($to_sql->getRows() == 1 || $to_cat_id == 0)
      {
        if ($to_sql->getRows() == 1)
        {
          $path = $to_sql->getValue('path').$to_sql->getValue('id').'|';
          $catname = $to_sql->getValue('name');
        }else
        {
          // In RootEbene
          $path = '|';
          $catname = $from_sql->getValue("name");
        }

        $art_sql = rex_sql::factory();
        $art_sql->setTable($REX['TABLE_PREFIX'].'article');
        if ($new_id == "") $new_id = $art_sql->setNewId('id');
        $art_sql->setValue('id', $new_id); // neuen auto_incrment erzwingen
        $art_sql->setValue('re_id', $to_cat_id);
        $art_sql->setValue('catname', $catname);
        $art_sql->setValue('catprior', 0);
        $art_sql->setValue('path', $path);
        $art_sql->setValue('prior', 99999); // Artikel als letzten Artikel in die neue Kat einfügen
        $art_sql->setValue('status', 0); // Kopierter Artikel offline setzen
        $art_sql->setValue('startpage', 0);
        $art_sql->addGlobalUpdateFields();
        $art_sql->addGlobalCreateFields();

        // schon gesetzte Felder nicht wieder überschreiben
        $dont_copy = array ('id', 'pid', 're_id', 'catname', 'catprior', 'path', 'prior', 'status', 'updatedate', 'updateuser', 'createdate', 'createuser', 'startpage');

        foreach (array_diff($from_sql->getFieldnames(), $dont_copy) as $fld_name)
        {
          $art_sql->setValue($fld_name, $from_sql->getValue($fld_name));
        }

        $art_sql->setValue("clang", $clang);
        $art_sql->insert();

        // TODO Doublecheck... is this really correct?
        $revisions = rex_sql::factory();
        $revisions->setQuery("select revision from ".$REX['TABLE_PREFIX']."article_slice where prior=1 AND ctype=1 AND article_id='$id' AND clang='$clang'");
        while($revisions->hasNext())
        {
          // ArticleSlices kopieren
          rex_copyContent($id, $new_id, $clang, $clang, 0, $revisions->getValue('revision'));
          $revisions->next();
        }

        // Prios neu berechnen
        rex_newArtPrio($to_cat_id, $clang, 1, 0);
      }
      else
      {
        return false;
      }
    }
    else
    {
      return false;
    }
  }

  // Caches des Artikels löschen, in allen Sprachen
  rex_deleteCacheArticle($id);

  // Caches der Kategorien löschen, da sich derin befindliche Artikel geändert haben
  rex_deleteCacheArticle($to_cat_id);

  return $new_id;
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
  global $REX;

  $id = (int) $id;
  $to_cat_id = (int) $to_cat_id;
  $from_cat_id = (int) $from_cat_id;

  if ($from_cat_id == $to_cat_id)
    return false;

  // Artikel in jeder Sprache verschieben
  foreach ($REX['CLANG'] as $clang => $clang_name)
  {
    // validierung der id & from_cat_id
    $from_sql = rex_sql::factory();
    $from_sql->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where clang="'. $clang .'" and startpage<>1 and id="'. $id .'" and re_id="'. $from_cat_id .'"');

    if ($from_sql->getRows() == 1)
    {
      // validierung der to_cat_id
      $to_sql = rex_sql::factory();
      $to_sql->setQuery('select * from '.$REX['TABLE_PREFIX'].'article where clang="'. $clang .'" and startpage=1 and id="'. $to_cat_id .'"');

      if ($to_sql->getRows() == 1 || $to_cat_id == 0)
      {
        if ($to_sql->getRows() == 1)
        {
          $re_id = $to_sql->getValue('id');
          $path = $to_sql->getValue('path').$to_sql->getValue('id').'|';
          $catname = $to_sql->getValue('name');
        }else
        {
          // In RootEbene
          $re_id = 0;
          $path = '|';
          $catname = $from_sql->getValue('name');
        }

        $art_sql = rex_sql::factory();
        //$art_sql->debugsql = 1;

        $art_sql->setTable($REX['TABLE_PREFIX'].'article');
        $art_sql->setValue('re_id', $re_id);
        $art_sql->setValue('path', $path);
        $art_sql->setValue('catname', $catname);
        // Artikel als letzten Artikel in die neue Kat einfügen
        $art_sql->setValue('prior', '99999');
        // Kopierter Artikel offline setzen
        $art_sql->setValue('status', '0');
        $art_sql->addGlobalUpdateFields();

        $art_sql->setWhere('clang="'. $clang .'" and startpage<>1 and id="'. $id .'" and re_id="'. $from_cat_id .'"');
        $art_sql->update();

        // Prios neu berechnen
        rex_newArtPrio($to_cat_id, $clang, 1, 0);
        rex_newArtPrio($from_cat_id, $clang, 1, 0);
      }
      else
      {
        return false;
      }
    }
    else
    {
      return false;
    }
  }

  // Caches des Artikels löschen, in allen Sprachen
  rex_deleteCacheArticle($id);

  // Caches der Kategorien löschen, da sich derin befindliche Artikel geändert haben
  rex_deleteCacheArticle($from_cat_id);
  rex_deleteCacheArticle($to_cat_id);

  return true;
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
  global $REX;

  $from_cat = (int) $from_cat;
  $to_cat = (int) $to_cat;

  if ($from_cat == $to_cat)
  {
    // kann nicht in gleiche kategroie kopiert werden
    return false;
  }
  else
  {
    // kategorien vorhanden ?
    // ist die zielkategorie im pfad der quellkategeorie ?
    $fcat = rex_sql::factory();
    $fcat->setQuery("select * from ".$REX['TABLE_PREFIX']."article where startpage=1 and id=$from_cat and clang=0");

    $tcat = rex_sql::factory();
    $tcat->setQuery("select * from ".$REX['TABLE_PREFIX']."article where startpage=1 and id=$to_cat and clang=0");

    if ($fcat->getRows()!=1 or ($tcat->getRows()!=1 && $to_cat != 0))
    {
      // eine der kategorien existiert nicht
      return false;
    }
    else
    {
      if ($to_cat>0)
      {
        $tcats = explode("|",$tcat->getValue("path"));
        if (in_array($from_cat,$tcats))
        {
          // zielkategorie ist in quellkategorie -> nicht verschiebbar
          return false;
        }
      }

      // ----- folgende cats regenerate
      $RC = array();
      $RC[$fcat->getValue("re_id")] = 1;
      $RC[$from_cat] = 1;
      $RC[$to_cat] = 1;

      if ($to_cat>0)
      {
        $to_path = $tcat->getValue("path").$to_cat."|";
        $to_re_id = $tcat->getValue("re_id");
      }
      else
      {
        $to_path = "|";
        $to_re_id = 0;
      }

      $from_path = $fcat->getValue("path").$from_cat."|";

      $gcats = rex_sql::factory();
      // $gcats->debugsql = 1;
      $gcats->setQuery("select * from ".$REX['TABLE_PREFIX']."article where path like '".$from_path."%' and clang=0");

      $up = rex_sql::factory();
      // $up->debugsql = 1;
      for($i=0;$i<$gcats->getRows();$i++)
      {
        // make update
        $new_path = $to_path.$from_cat."|".str_replace($from_path,"",$gcats->getValue("path"));
        $icid = $gcats->getValue("id");
        $irecid = $gcats->getValue("re_id");

        // path aendern und speichern
        $up->setTable($REX['TABLE_PREFIX']."article");
        $up->setWhere("id=$icid");
        $up->setValue("path",$new_path);
        $up->update();

        // cat in gen eintragen
        $RC[$icid] = 1;

        $gcats->next();
      }

      // ----- clang holen, max catprio holen und entsprechen updaten
      $gmax = rex_sql::factory();
      $up = rex_sql::factory();
      // $up->debugsql = 1;
      foreach($REX['CLANG'] as $clang => $clang_name)
      {
        $gmax->setQuery("select max(catprior) from ".$REX['TABLE_PREFIX']."article where re_id=$to_cat and clang=".$clang);
        $catprior = (int) $gmax->getValue("max(catprior)");
        $up->setTable($REX['TABLE_PREFIX']."article");
        $up->setWhere("id=$from_cat and clang=$clang ");
        $up->setValue("path",$to_path);
        $up->setValue("re_id",$to_cat);
        $up->setValue("catprior",($catprior+1));
        $up->update();
      }

      // ----- generiere artikel neu - ohne neue inhaltsgenerierung
      foreach($RC as $id => $key)
      {
        rex_deleteCacheArticle($id);
      }

      foreach($REX['CLANG'] as $clang => $clang_name)
      {
        rex_newCatPrio($fcat->getValue("re_id"),$clang,0,1);
      }
    }
  }

  return true;
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
  global $REX;

  foreach($REX['CLANG'] as $_clang => $clang_name)
  {
    if($clang !== null && $clang != $_clang)
      continue;

    $CONT = new rex_article_base();
    $CONT->setCLang($_clang);
    $CONT->setEval(FALSE); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
    if (!$CONT->setArticleId($article_id)) return FALSE;

    // --------------------------------------------------- Artikelcontent speichern
    $article_content_file = rex_path::generated("articles/$article_id.$_clang.content");
    $article_content = $CONT->getArticle();

    // ----- EXTENSION POINT
    $article_content = rex_register_extension_point('GENERATE_FILTER', $article_content,
      array (
        'id' => $article_id,
        'clang' => $_clang,
        'article' => $CONT
      )
    );

    if (rex_put_file_contents($article_content_file, $article_content) === FALSE)
    {
      return $REX['I18N']->msg('article_could_not_be_generated')." ".$REX['I18N']->msg('check_rights_in_directory').rex_path::generated('articles/');
    }
  }

  return TRUE;
}