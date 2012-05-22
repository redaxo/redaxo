<?php

class rex_article_service
{
  /**
   * Erstellt einen neuen Artikel
   *
   * @param array $data Array mit den Daten des Artikels
   *
   * @return string Eine Statusmeldung
   */
  static public function addArticle($data)
  {
    $message = '';

    if(!is_array($data))
    {
      throw  new rex_api_exception('Expecting $data to be an array!');
    }

    self::reqKey($data, 'category_id');
    self::reqKey($data, 'prior');
    self::reqKey($data, 'name');

    if($data['prior'] <= 0)
    {
      $data['prior'] = 1;
    }

    // parent may be null, when adding in the root cat
    $parent = rex_ooCategory::getCategoryById($data['category_id']);
    if($parent)
    {
      $path = $parent->getPath();
      $path .= $parent->getId(). '|';
    }
    else
    {
      $path = '|';
    }

    $templates = rex_ooCategory::getTemplates($data['category_id']);

    // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
    // oder leer setzen.
    if(!isset($templates[$data['template_id']]))
    {
      $data['template_id'] = 0;
      if(count($templates)>0)
      {
        $data['template_id'] = key($templates);
      }
    }

    $message = rex_i18n::msg('article_added');

    $AART = rex_sql::factory();
    unset($id);
    foreach(rex_clang::getAllIds() as $key)
    {
      // ------- Kategorienamen holen
      $category = rex_ooCategory::getCategoryById($data['category_id'], $key);

      $categoryName = '';
      if($category)
      {
        $categoryName = $category->getName();
      }

      $AART->setTable(rex::getTablePrefix().'article');
      if (!isset ($id) || !$id)
      {
        $id = $AART->setNewId('id');
      }
      else
      {
        $AART->setValue('id', $id);
      }
      $AART->setValue('name', $data['name']);
      $AART->setValue('catname', $categoryName);
      $AART->setValue('attributes', '');
      $AART->setValue('clang', $key);
      $AART->setValue('re_id', $data['category_id']);
      $AART->setValue('prior', $data['prior']);
      $AART->setValue('path', $path);
      $AART->setValue('startpage', 0);
      $AART->setValue('status', 0);
      $AART->setValue('template_id', $data['template_id']);
      $AART->addGlobalCreateFields();
      $AART->addGlobalUpdateFields();

      try {
        $AART->insert();
        // ----- PRIOR
        self::newArtPrio($data['category_id'], $key, 0, $data['prior']);
      } catch (rex_sql_exception $e) {
        throw new rex_api_exception($e);
      }

      // ----- EXTENSION POINT
      $message = rex_extension::registerPoint('ART_ADDED', $message,
      array (
        'id' => $id,
        'clang' => $key,
        'status' => 0,
        'name' => $data['name'],
        're_id' => $data['category_id'],
        'prior' => $data['prior'],
        'path' => $path,
        'template_id' => $data['template_id'],
        'data' => $data,
      )
      );
    }

    return $message;
  }

  /**
   * Bearbeitet einen Artikel
   *
   * @param int   $article_id  Id des Artikels der verändert werden soll
   * @param int   $clang       Id der Sprache
   * @param array $data        Array mit den Daten des Artikels
   *
   * @return string Eine Statusmeldung
   */
  static public function editArticle($article_id, $clang, $data)
  {
    $message = '';

    if(!is_array($data))
    {
      throw  new rex_api_exception('Expecting $data to be an array!');
    }

    self::reqKey($data, 'name');

    // Artikel mit alten Daten selektieren
    $thisArt = rex_sql::factory();
    $thisArt->setQuery('select * from '.rex::getTablePrefix().'article where id='.$article_id.' and clang='. $clang);

    if ($thisArt->getRows() != 1)
    {
      throw new rex_api_exception('Unable to find article with id "'. $article_id .'" and clang "'. $clang .'"!');
    }

    $ooArt = rex_ooArticle::getArticleById($article_id, $clang);
    $data['category_id'] = $ooArt->getCategoryId();
    $templates = rex_ooCategory::getTemplates($data['category_id']);

    // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
    // oder leer setzen.
    if(!isset($templates[$data['template_id']]))
    {
      $data['template_id'] = 0;
      if(count($templates)>0)
      {
        $data['template_id'] = key($templates);
      }
    }

    if(isset($data['prior']))
    {
      if($data['prior'] <= 0)
      {
        $data['prior'] = 1;
      }
    }

    // complete remaining optional aprams
    foreach(array('path', 'prior') as $optionalData)
    {
      if(!isset($data[$optionalData]))
      {
        $data[$optionalData] = $thisArt->getValue($optionalData);
      }
    }

    $EA = rex_sql::factory();
    $EA->setTable(rex::getTablePrefix()."article");
    $EA->setWhere(array('id' => $article_id, 'clang'=> $clang));
    $EA->setValue('name', $data['name']);
    $EA->setValue('template_id', $data['template_id']);
    $EA->setValue('prior', $data['prior']);
    $EA->addGlobalUpdateFields();

    try {
      $EA->update();
      $message = rex_i18n::msg('article_updated');

      // ----- PRIOR
      self::newArtPrio($data['category_id'], $clang, $data['prior'], $thisArt->getValue('prior'));
      rex_article_cache::delete($article_id, $clang);

      // ----- EXTENSION POINT
      $message = rex_extension::registerPoint('ART_UPDATED', $message,
        array (
          'id' => $article_id,
          'article' => clone($EA),
          'article_old' => clone($thisArt),
          'status' => $thisArt->getValue('status'),
          'name' => $data['name'],
          'clang' => $clang,
          're_id' => $data['category_id'],
          'prior' => $data['prior'],
          'path' => $data['path'],
          'template_id' => $data['template_id'],
          'data' => $data,
        )
      );
    } catch (rex_sql_exception $e) {
      throw new rex_api_exception($e);
    }

    return $message;
  }

  /**
   * Löscht einen Artikel und reorganisiert die Prioritäten verbleibender Geschwister-Artikel
   *
   * @param int $article_id Id des Artikels die gelöscht werden soll
   *
   * @return string Eine Statusmeldung
   */
  static public function deleteArticle($article_id)
  {
    $Art = rex_sql::factory();
    $Art->setQuery('select * from '.rex::getTablePrefix().'article where id='.$article_id.' and startpage=0');

    $message = '';
    if ($Art->getRows() > 0)
    {
      $message = self::_deleteArticle($article_id);
      $re_id = $Art->getValue("re_id");

      foreach(rex_clang::getAllIds() as $clang)
      {
        // ----- PRIOR
        self::newArtPrio($Art->getValue("re_id"), $clang, 0, 1);

        // ----- EXTENSION POINT
        $message = rex_extension::registerPoint('ART_DELETED', $message,
        array (
          "id"          => $article_id,
          "clang"       => $clang,
          "re_id"       => $re_id,
          'name'        => $Art->getValue('name'),
          'status'      => $Art->getValue('status'),
          'prior'       => $Art->getValue('prior'),
          'path'        => $Art->getValue('path'),
          'template_id' => $Art->getValue('template_id'),
        )
        );

        $Art->next();
      }
    }
    else
    {
      throw new rex_api_exception(rex_i18n::msg('article_doesnt_exist'));
    }

    return $message;
  }

  /**
   * Löscht einen Artikel
   *
   * @param $id ArtikelId des Artikels, der gelöscht werden soll
   *
   * @return string Eine Statusmeldung
   */
  static public function _deleteArticle($id)
  {
    // artikel loeschen
    //
    // kontrolle ob erlaubnis nicht hier.. muss vorher geschehen
    //
    // -> startpage = 0
    // --> artikelfiles löschen
    // ---> article
    // ---> content
    // ---> clist
    // ---> alist
    // -> startpage = 1
    // --> rekursiv aufrufen

    if ($id == rex::getProperty('start_article_id'))
    {
      throw new rex_api_exception(rex_i18n::msg('cant_delete_sitestartarticle'));
    }
    if ($id == rex::getProperty('notfound_article_id'))
    {
      throw new rex_api_exception(rex_i18n::msg('cant_delete_notfoundarticle'));
    }

    $ART = rex_sql::factory();
    $ART->setQuery('select * from '.rex::getTablePrefix().'article where id='.$id.' and clang=0');

    $message = '';
    if ($ART->getRows() > 0)
    {
      $re_id = $ART->getValue('re_id');
      $message = rex_extension::registerPoint('ART_PRE_DELETED', $message, array (
                    "id"          => $id,
                    "re_id"       => $re_id,
                    'name'        => $ART->getValue('name'),
                    'status'      => $ART->getValue('status'),
                    'prior'       => $ART->getValue('prior'),
                    'path'        => $ART->getValue('path'),
                    'template_id' => $ART->getValue('template_id')
      )
      );

      if ($ART->getValue('startpage') == 1)
      {
        $message = rex_i18n::msg('category_deleted');
        $SART = rex_sql::factory();
        $SART->setQuery('select * from '.rex::getTablePrefix().'article where re_id='.$id.' and clang=0');
        for ($i = 0; $i < $SART->getRows(); $i ++)
        {
          self::_deleteArticle($id);
          $SART->next();
        }
      }else
      {
        $message = rex_i18n::msg('article_deleted');
      }

      rex_article_cache::delete($id);
      $ART->setQuery('delete from '.rex::getTablePrefix().'article where id='.$id);
      $ART->setQuery('delete from '.rex::getTablePrefix().'article_slice where article_id='.$id);

      // --------------------------------------------------- Listen generieren
      rex_article_cache::generateLists($re_id);

      return $message;
    }
    else
    {
      throw new rex_api_exception(rex_i18n::msg('category_doesnt_exist'));
    }
  }


  /**
   * Ändert den Status des Artikels
   *
   * @param int       $article_id Id des Artikels die gelöscht werden soll
   * @param int       $clang      Id der Sprache
   * @param int|null  $status     Status auf den der Artikel gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
   *
   * @return int Der neue Status des Artikels
   */
  static public function articleStatus($article_id, $clang, $status = null)
  {
    $message = '';

    $GA = rex_sql::factory();
    $GA->setQuery("select * from ".rex::getTablePrefix()."article where id='$article_id' and clang=$clang");
    if ($GA->getRows() == 1)
    {
      // Status wurde nicht von außen vorgegeben,
      // => zyklisch auf den nächsten Weiterschalten
      if(!$status)
      $newstatus = self::nextStatus($GA->getValue('status'));
      else
      $newstatus = $status;

      $EA = rex_sql::factory();
      $EA->setTable(rex::getTablePrefix()."article");
      $EA->setWhere(array('id' => $article_id, 'clang' => $clang));
      $EA->setValue('status', $newstatus);
      $EA->addGlobalUpdateFields(rex::isBackend() ? null : 'frontend');

      try {
        $EA->update();

        rex_article_cache::delete($article_id, $clang);

        // ----- EXTENSION POINT
        rex_extension::registerPoint('ART_STATUS', null, array (
        'id' => $article_id,
        'clang' => $clang,
        'status' => $newstatus
        ));
      } catch (rex_sql_exception $e) {
        throw new rex_api_exception($e);
      }
    }
    else
    {
      throw new rex_api_exception(rex_i18n::msg("no_such_category"));
    }

    return $newstatus;
  }

  /**
   * Gibt alle Stati zurück, die für einen Artikel gültig sind
   *
   * @return array Array von Stati
   */
  static public function statusTypes()
  {
    static $artStatusTypes;

    if(!$artStatusTypes)
    {
      $artStatusTypes = array(
        // Name, CSS-Class
        array(rex_i18n::msg('status_offline'), 'rex-offline'),
        array(rex_i18n::msg('status_online'), 'rex-online')
      );

      // ----- EXTENSION POINT
      $artStatusTypes = rex_extension::registerPoint('ART_STATUS_TYPES', $artStatusTypes);
    }

    return $artStatusTypes;
  }

  static public function nextStatus($currentStatus)
  {
    $artStatusTypes = self::statusTypes();
    return ($currentStatus + 1) % count($artStatusTypes);
  }

  static public function prevStatus($currentStatus)
  {
    $artStatusTypes = self::statusTypes();
    if(($currentStatus - 1) < 0 ) return count($artStatusTypes) - 1;

    return ($currentStatus - 1) % count($artStatusTypes);
  }

  /**
   * Berechnet die Prios der Artikel in einer Kategorie neu
   *
   * @param $re_id    KategorieId der Kategorie, die erneuert werden soll
   * @param $clang    ClangId der Kategorie, die erneuert werden soll
   * @param $new_prio Neue PrioNr der Kategorie
   * @param $old_prio Alte PrioNr der Kategorie
   *
   * @return void
   */
  static public function newArtPrio($re_id, $clang, $new_prio, $old_prio)
  {
    if ($new_prio != $old_prio)
    {
      if ($new_prio < $old_prio)
        $addsql = "desc";
      else
        $addsql = "asc";

      rex_sql_util::organizePriorities(
        rex::getTable('article'),
        'prior',
        'clang='. $clang .' AND ((startpage<>1 AND re_id='. $re_id .') OR (startpage=1 AND id='. $re_id .'))',
        'prior,updatedate '. $addsql,
        'pid'
      );

      rex_article_cache::deleteLists($re_id, $clang);
    }
  }

  /**
   * Konvertiert einen Artikel in eine Kategorie
   *
   * @param int $art_id  Artikel ID des Artikels, der in eine Kategorie umgewandelt werden soll
   *
   * @return boolean TRUE bei Erfolg, sonst FALSE
   */
  static public function article2category($art_id)
  {
    $sql = rex_sql::factory();

    // LANG SCHLEIFE
    foreach(rex_clang::getAllIds() as $clang)
    {
      // artikel
      $sql->setQuery('select re_id, name from '.rex::getTablePrefix()."article where id=$art_id and startpage=0 and clang=$clang");

      if (!isset($re_id))
        $re_id = $sql->getValue('re_id');

      // artikel updaten
      $sql->setTable(rex::getTablePrefix()."article");
      $sql->setWhere(array('id' => $art_id, 'clang' => $clang));
      $sql->setValue('startpage', 1);
      $sql->setValue('catname', $sql->getValue('name'));
      $sql->setValue('catprior', 100);
      $sql->update();

      rex_category_service::newCatPrio($re_id, $clang, 0, 100);
    }

    rex_article_cache::deleteLists($re_id);
    rex_article_cache::delete($art_id);

    foreach(rex_clang::getAllIds() as $clang)
    {
      rex_extension::registerPoint('ART_TO_CAT', '', array (
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
  static public function category2article($art_id)
  {
    $sql = rex_sql::factory();

    // Kategorie muss leer sein
    $sql->setQuery('SELECT pid FROM '. rex::getTablePrefix() .'article WHERE re_id='. $art_id .' LIMIT 1');
    if ($sql->getRows() != 0)
      return false;

    // LANG SCHLEIFE
    foreach(rex_clang::getAllIds() as $clang)
    {
      // artikel
      $sql->setQuery('select re_id, name from '.rex::getTablePrefix()."article where id=$art_id and startpage=1 and clang=$clang");

      if (!isset($re_id))
      $re_id = $sql->getValue('re_id');

      // artikel updaten
      $sql->setTable(rex::getTablePrefix()."article");
      $sql->setWhere(array('id' => $art_id, 'clang' => $clang));
      $sql->setValue('startpage', 0);
      $sql->setValue('prior', 100);
      $sql->update();

      rex_article_service::newArtPrio($re_id, $clang, 0, 100);
    }

    rex_article_cache::deleteLists($re_id);
    rex_article_cache::delete($art_id);

    foreach(rex_clang::getAllIds() as $clang)
    {
      rex_extension::registerPoint('CAT_TO_ART', '', array (
        'id' => $art_id,
        'clang' => $clang,
      ));
    }

    return true;
  }

  /**
  * Konvertiert einen Artikel zum Startartikel der eigenen Kategorie
  *
  * @param int $neu_id  Artikel ID des Artikels, der Startartikel werden soll
  *
  * @return boolean TRUE bei Erfolg, sonst FALSE
  */
  static public function article2startpage($neu_id)
  {
    $GAID = array();

    // neuen startartikel holen und schauen ob da
    $neu = rex_sql::factory();
    $neu->setQuery("select * from ".rex::getTablePrefix()."article where id=$neu_id and startpage=0 and clang=0");
    if ($neu->getRows()!=1) return false;
    $neu_path = $neu->getValue("path");
    $neu_cat_id = $neu->getValue("re_id");

    // in oberster kategorie dann return
    if ($neu_cat_id == 0) return false;

    // alten startartikel
    $alt = rex_sql::factory();
    $alt->setQuery("select * from ".rex::getTablePrefix()."article where id=$neu_cat_id and startpage=1 and clang=0");
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
    foreach(rex_clang::getAllIds() as $clang)
    {
      // alter startartikel
      $alt->setQuery("select * from ".rex::getTablePrefix()."article where id=$neu_cat_id and startpage=1 and clang=$clang");

      // neuer startartikel
      $neu->setQuery("select * from ".rex::getTablePrefix()."article where id=$neu_id and startpage=0 and clang=$clang");

      // alter startartikel updaten
      $alt2 = rex_sql::factory();
      $alt2->setTable(rex::getTablePrefix()."article");
      $alt2->setWhere(array('id' => $alt_id, 'clang' => $clang));
      $alt2->setValue("re_id",$neu_id);

      // neuer startartikel updaten
      $neu2 = rex_sql::factory();
      $neu2->setTable(rex::getTablePrefix()."article");
      $neu2->setWhere(array('id' => $neu_id, 'clang' => $clang));
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
    $articles->setQuery("select * from ".rex::getTablePrefix()."article where path like '%|$alt_id|%'");
    for($i=0;$i<$articles->getRows();$i++)
    {
      $iid = $articles->getValue("id");
      $ipath = str_replace("|$alt_id|","|$neu_id|",$articles->getValue("path"));

      $ia->setTable(rex::getTablePrefix()."article");
      $ia->setWhere(array('id' => $iid));
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
      rex_article_cache::delete($gid);
    }

    rex_complex_perm::replaceItem('structure', $alt_id, $neu_id);

    foreach(rex_clang::getAllIds() as $clang)
    {
      rex_extension::registerPoint('ART_TO_STARTPAGE', '', array (
        'id' => $neu_id,
        'id_old' => $alt_id,
        'clang' => $clang,
      ));
    }

    return true;
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
            // FIXME this dependency is very ugly!
            // ArticleSlices kopieren
            rex_content_service::copyContent($id, $new_id, $clang, $clang, 0, $rev->getValue('revision'));
          }

          // Prios neu berechnen
          self::newArtPrio($to_cat_id, $clang, 1, 0);
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
          self::newArtPrio($to_cat_id, $clang, 1, 0);
          self::newArtPrio($from_cat_id, $clang, 1, 0);
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
   * Checks whether the required array key $keyName isset
   *
   * @param array $array The array
   * @param string $keyName The key
   */
  static protected function reqKey($array, $keyName)
  {
    if(!isset($array[$keyName]))
    {
      throw new rex_api_exception('Missing required parameter "'. $keyName .'"!');
    }
  }
}
