<?php

class rex_content_service
{
  /**
   * Verschiebt einen Slice
   *
   * @param int    $slice_id  Id des Slices
   * @param int    $clang     Id der Sprache
   * @param string $direction Richtung in die verschoben werden soll
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function moveSlice($slice_id, $clang, $direction)
  {
    // ctype beachten
    // verschieben / vertauschen
    // article regenerieren.
  
    $success = false;
    $message = rex_i18n::msg('slice_moved_error');
  
    // check if slice id is valid
    $CM = rex_sql::factory();
    $CM->setQuery("select * from " . rex::getTablePrefix() . "article_slice where id='$slice_id' and clang=$clang");
    if ($CM->getRows() == 1)
    {
      // prepare sql for later saving
      $upd = rex_sql::factory();
      $upd->setTable(rex::getTablePrefix() . "article_slice");
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
        rex::getTablePrefix() . 'article_slice',
          'prior',
          'article_id=' . $article_id . ' AND clang=' . $clang .' AND ctype='. $ctype .' AND revision='. $slice_revision,
          'prior, updatedate '. $updSort
        );
  
        rex_article_cache::deleteContent($article_id, $clang);
  
        $message = rex_i18n::msg('slice_moved');
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
   * Verschiebt einen Slice nach oben
   *
   * @param int $slice_id Id des Slices
   * @param int $clang    Id der Sprache
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function moveSliceUp($slice_id, $clang)
  {
    return self::moveSlice($slice_id, $clang, 'moveup');
  }
  
  /**
   * Verschiebt einen Slice nach unten
   *
   * @param int $slice_id Id des Slices
   * @param int $clang    Id der Sprache
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function moveSliceDown($slice_id, $clang)
  {
    return self::moveSlice($slice_id, $clang, 'movedown');
  }
  
  /**
   * Löscht einen Slice
   *
   * @param int    $slice_id  Id des Slices
   *
   * @return boolean TRUE bei Erfolg, sonst FALSE
   */
  static public function deleteSlice($slice_id)
  {
    // check if slice id is valid
    $curr = rex_sql::factory();
    $curr->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article_slice WHERE id=' . $slice_id);
    if($curr->getRows() != 1)
    {
      return false;
    }
  
    // delete the slice
    $del = rex_sql::factory();
    $del->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'article_slice WHERE id=' . $slice_id);
  
    // reorg remaining slices
    rex_organize_priorities(
    rex::getTablePrefix() . 'article_slice',
      'prior',
      'article_id=' . $curr->getValue('article_id') . ' AND clang=' . $curr->getValue('clang') .' AND ctype='. $curr->getValue('ctype') .' AND revision='. $curr->getValue('revision'),
      'prior'
    );
  
    // check if delete was successfull
    return $curr->getRows() == 1;
  }
  
  /**
  * Kopiert eine Kategorie in eine andere
  *
  * @param int $from_cat_id KategorieId der Kategorie, die kopiert werden soll (Quelle)
  * @param int $to_cat_id   KategorieId der Kategorie, IN die kopiert werden soll (Ziel)
  */
  static public function copyCategory($from_cat, $to_cat)
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
  static public function copyMeta($from_id, $to_id, $from_clang = 0, $to_clang = 0, $params = array ())
  {
    $from_clang = (int) $from_clang;
    $to_clang = (int) $to_clang;
    $from_id = (int) $from_id;
    $to_id = (int) $to_id;
    if (!is_array($params))
    $params = array ();
  
    if ($from_id == $to_id && $from_clang == $to_clang)
    return false;
  
    $gc = rex_sql::factory();
    $gc->setQuery("select * from ".rex::getTablePrefix()."article where clang='$from_clang' and id='$from_id'");
  
    if ($gc->getRows() == 1)
    {
      $uc = rex_sql::factory();
      // $uc->debugsql = 1;
      $uc->setTable(rex::getTablePrefix()."article");
      $uc->setWhere("clang='$to_clang' and id='$to_id'");
      $uc->addGlobalUpdateFields();
  
      foreach ($params as $key => $value)
      {
        $uc->setValue($value, $gc->getValue($value));
      }
  
      $uc->update();
  
      rex_article_cache::deleteMeta($to_id,$to_clang);
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
  static public function copyContent($from_id, $to_id, $from_clang = 0, $to_clang = 0, $from_re_sliceid = 0, $revision = 0)
  {
    if ($from_id == $to_id && $from_clang == $to_clang)
    return false;
  
    $gc = rex_sql::factory();
    $gc->setQuery("select * from ".rex::getTablePrefix()."article_slice where article_id='$from_id' and clang='$from_clang' and revision='$revision'");
  
    if ($gc->getRows() > 0)
    {
      $ins = rex_sql::factory();
      $ins->setTable(rex::getTablePrefix()."article_slice");
      $ctypes = array();
  
      $cols = rex_sql::factory();
      // $cols->debugsql = 1;
      $cols->setquery("SHOW COLUMNS FROM ".rex::getTablePrefix()."article_slice");
      foreach($gc as $slice)
      {
        foreach($cols as $col)
        {
          $colname = $col->getValue("Field");
          if ($colname == "clang") $value = $to_clang;
          elseif ($colname == "article_id") $value = $to_id;
          else
          $value = $slice->getValue($colname);
  
          // collect all affected ctypes
          if ($colname == "ctype")
          $ctypes[$value] = $value;
  
          if ($colname != "id")
          $ins->setValue($colname, $value);
        }
  
        $ins->addGlobalUpdateFields();
        $ins->addGlobalCreateFields();
        $ins->insert();
      }
  
      foreach($ctypes as $ctype)
      {
        // reorg slices
        rex_organize_priorities(
        rex::getTablePrefix() . 'article_slice',
          'prior',
          'article_id=' . $to_id . ' AND clang=' . $to_clang .' AND ctype='. $ctype .' AND revision='. $revision,
          'prior, updatedate'
        );
      }
  
      rex_article_cache::deleteContent($to_id, $to_clang);
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
  static public function copyArticle($id, $to_cat_id)
  {
    $id = (int) $id;
    $to_cat_id = (int) $to_cat_id;
    $new_id = '';
  
    // Artikel in jeder Sprache kopieren
    foreach(rex_clang::getAllIds() as $clang)
    {
      // validierung der id & from_cat_id
      $from_sql = rex_sql::factory();
      $qry = 'select * from '.rex::getTablePrefix().'article where clang="'.$clang.'" and id="'. $id .'"';
      $from_sql->setQuery($qry);
  
      if ($from_sql->getRows() == 1)
      {
        // validierung der to_cat_id
        $to_sql = rex_sql::factory();
        $to_sql->setQuery('select * from '.rex::getTablePrefix().'article where clang="'.$clang.'" and startpage=1 and id="'. $to_cat_id .'"');
  
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
          $art_sql->setTable(rex::getTablePrefix().'article');
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
          $revisions->setQuery("select revision from ".rex::getTablePrefix()."article_slice where prior=1 AND ctype=1 AND article_id='$id' AND clang='$clang'");
          foreach($revisions as $rev)
          {
            // ArticleSlices kopieren
            self::copyContent($id, $new_id, $clang, $clang, 0, $rev->getValue('revision'));
          }
  
          // Prios neu berechnen
          rex_article_service::newArtPrio($to_cat_id, $clang, 1, 0);
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
    rex_article_cache::delete($id);
  
    // Caches der Kategorien löschen, da sich derin befindliche Artikel geändert haben
    rex_article_cache::delete($to_cat_id);
  
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
  static public function moveArticle($id, $from_cat_id, $to_cat_id)
  {
    $id = (int) $id;
    $to_cat_id = (int) $to_cat_id;
    $from_cat_id = (int) $from_cat_id;
  
    if ($from_cat_id == $to_cat_id)
    return false;
  
    // Artikel in jeder Sprache verschieben
    foreach (rex_clang::getAllIds() as $clang)
    {
      // validierung der id & from_cat_id
      $from_sql = rex_sql::factory();
      $from_sql->setQuery('select * from '.rex::getTablePrefix().'article where clang="'. $clang .'" and startpage<>1 and id="'. $id .'" and re_id="'. $from_cat_id .'"');
  
      if ($from_sql->getRows() == 1)
      {
        // validierung der to_cat_id
        $to_sql = rex_sql::factory();
        $to_sql->setQuery('select * from '.rex::getTablePrefix().'article where clang="'. $clang .'" and startpage=1 and id="'. $to_cat_id .'"');
  
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
  
          $art_sql->setTable(rex::getTablePrefix().'article');
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
          rex_article_service::newArtPrio($to_cat_id, $clang, 1, 0);
          rex_article_service::newArtPrio($from_cat_id, $clang, 1, 0);
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
    rex_article_cache::delete($id);
  
    // Caches der Kategorien löschen, da sich derin befindliche Artikel geändert haben
    rex_article_cache::delete($from_cat_id);
    rex_article_cache::delete($to_cat_id);
  
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
  static public function moveCategory($from_cat, $to_cat)
  {
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
      $fcat->setQuery("select * from ".rex::getTablePrefix()."article where startpage=1 and id=$from_cat and clang=0");
  
      $tcat = rex_sql::factory();
      $tcat->setQuery("select * from ".rex::getTablePrefix()."article where startpage=1 and id=$to_cat and clang=0");
  
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
        $gcats->setQuery("select * from ".rex::getTablePrefix()."article where path like '".$from_path."%' and clang=0");
  
        $up = rex_sql::factory();
        // $up->debugsql = 1;
        for($i=0;$i<$gcats->getRows();$i++)
        {
          // make update
          $new_path = $to_path.$from_cat."|".str_replace($from_path,"",$gcats->getValue("path"));
          $icid = $gcats->getValue("id");
          $irecid = $gcats->getValue("re_id");
  
          // path aendern und speichern
          $up->setTable(rex::getTablePrefix()."article");
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
        foreach(rex_clang::getAllIds() as $clang)
        {
          $gmax->setQuery("select max(catprior) from ".rex::getTablePrefix()."article where re_id=$to_cat and clang=".$clang);
          $catprior = (int) $gmax->getValue("max(catprior)");
          $up->setTable(rex::getTablePrefix()."article");
          $up->setWhere("id=$from_cat and clang=$clang ");
          $up->setValue("path",$to_path);
          $up->setValue("re_id",$to_cat);
          $up->setValue("catprior",($catprior+1));
          $up->update();
        }
  
        // ----- generiere artikel neu - ohne neue inhaltsgenerierung
        foreach($RC as $id => $key)
        {
          rex_article_cache::delete($id);
        }
  
        foreach(rex_clang::getAllIds() as $clang)
        {
          rex_category_service::newCatPrio($fcat->getValue("re_id"),$clang,0,1);
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
  static public function generateArticleContent($article_id, $clang = null)
  {
    foreach(rex_clang::getAllIds() as $_clang)
    {
      if($clang !== null && $clang != $_clang)
      continue;
  
      $CONT = new rex_article_base();
      $CONT->setCLang($_clang);
      $CONT->setEval(FALSE); // Content nicht ausführen, damit in Cachedatei gespeichert werden kann
      if (!$CONT->setArticleId($article_id)) return FALSE;
  
      // --------------------------------------------------- Artikelcontent speichern
      $article_content_file = rex_path::cache("articles/$article_id.$_clang.content");
      $article_content = $CONT->getArticle();
  
      // ----- EXTENSION POINT
      $article_content = rex_extension::registerPoint('GENERATE_FILTER', $article_content,
      array (
          'id' => $article_id,
          'clang' => $_clang,
          'article' => $CONT
      )
      );
  
      if (rex_file::put($article_content_file, $article_content) === FALSE)
      {
        return rex_i18n::msg('article_could_not_be_generated')." ".rex_i18n::msg('check_rights_in_directory').rex_path::cache('articles/');
      }
    }
  
    return TRUE;
  }  
}