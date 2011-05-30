<?php

class rex_article_service
{
  /**
   * Erstellt einen neuen Artikel
   *
   * @param array $data Array mit den Daten des Artikels
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
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

      if($AART->insert())
      {
        // ----- PRIOR
        self::newArtPrio($data['category_id'], $key, 0, $data['prior']);
      }
      else
      {
        throw new rex_api_exception($AART->getError());
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
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function editArticle($article_id, $clang, $data)
  {
    $message = '';

    if(!is_array($data))
    {
      throw  new rex_api_exception('Expecting $data to be an array!');
    }

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

    $EA = rex_sql::factory();
    $EA->setTable(rex::getTablePrefix()."article");
    $EA->setWhere("id='$article_id' and clang=$clang");
    $EA->setValue('name', $data['name']);
    $EA->setValue('template_id', $data['template_id']);
    $EA->setValue('prior', $data['prior']);
    $EA->addGlobalUpdateFields();

    if($EA->update())
    {
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
    }
    else
    {
      throw new rex_api_exception($EA->getError());
    }

    return $message;
  }

  /**
   * Löscht einen Artikel und reorganisiert die Prioritäten verbleibender Geschwister-Artikel
   *
   * @param int $article_id Id des Artikels die gelöscht werden soll
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
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
   * @return Erfolgsmeldung bzw. Fehlermeldung bei Fehlern.
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
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function articleStatus($article_id, $clang, $status = null)
  {
    $message = '';
    $artStatusTypes = self::statusTypes();

    $GA = rex_sql::factory();
    $GA->setQuery("select * from ".rex::getTablePrefix()."article where id='$article_id' and clang=$clang");
    if ($GA->getRows() == 1)
    {
      // Status wurde nicht von außen vorgegeben,
      // => zyklisch auf den nächsten Weiterschalten
      if(!$status)
      $newstatus = ($GA->getValue('status') + 1) % count($artStatusTypes);
      else
      $newstatus = $status;

      $EA = rex_sql::factory();
      $EA->setTable(rex::getTablePrefix()."article");
      $EA->setWhere("id='$article_id' and clang=$clang");
      $EA->setValue('status', $newstatus);
      $EA->addGlobalUpdateFields(rex::isBackend() ? null : 'frontend');

      if($EA->update())
      {
        $message = rex_i18n::msg('article_status_updated');
        rex_article_cache::delete($article_id, $clang);

        // ----- EXTENSION POINT
        $message = rex_extension::registerPoint('ART_STATUS', $message, array (
        'id' => $article_id,
        'clang' => $clang,
        'status' => $newstatus
        ));
      }
      else
      {
        throw new rex_api_exception($EA->getError());
      }
    }
    else
    {
      throw new rex_api_exception(rex_i18n::msg("no_such_category"));
    }

    return $message;
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

      rex_organize_priorities(
      rex::getTablePrefix().'article',
      'prior',
      'clang='. $clang .' AND ((startpage<>1 AND re_id='. $re_id .') OR (startpage=1 AND id='. $re_id .'))',
      'prior,updatedate '. $addsql,
      'pid'
      );

      rex_article_cache::deleteLists($re_id, $clang);
    }
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