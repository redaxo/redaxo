<?php

/**
 * Funktionensammlung für die Strukturverwaltung
 *
 * @package redaxo5
 * @version svn:$Id$
 */
class rex_category_service
{
  /**
   * Erstellt eine neue Kategorie
   *
   * @param int   $category_id KategorieId in der die neue Kategorie erstellt werden soll
   * @param array $data        Array mit den Daten der Kategorie
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function addCategory($category_id, $data)
  {
    global $REX;

    if(!is_array($data))
      throw new rexApiException('Expecting $data to be an array!');

    $startpageTemplates = array();
    if ($category_id != "")
    {
      // TemplateId vom Startartikel der jeweiligen Sprache vererben
      $sql = rex_sql::factory();
      // $sql->debugsql = 1;
      $sql->setQuery("select clang,template_id from ".$REX['TABLE_PREFIX']."article where id=$category_id and startpage=1");
      for ($i = 0; $i < $sql->getRows(); $i++, $sql->next())
      {
        $startpageTemplates[$sql->getValue("clang")] = $sql->getValue("template_id");
      }
    }

    // TODO add checks for req. parameters
    if(isset($data['catprior']))
    {
      if($data['catprior'] <= 0)
      $data['catprior'] = 1;
    }

    if(!isset($data['name']))
    {
      $data['name'] = $data['catname'];
    }

    if(!isset($data['status']))
    {
      $data['status'] = 0;
    }

    // Alle Templates der Kategorie
    $templates = rex_ooCategory::getTemplates($category_id);
    // Kategorie in allen Sprachen anlegen
    $AART = rex_sql::factory();
    foreach($REX['CLANG'] as $key => $val)
    {
      $template_id = $REX['DEFAULT_TEMPLATE_ID'];
      if(isset ($startpageTemplates[$key]) && $startpageTemplates[$key] != '')
      $template_id = $startpageTemplates[$key];

      // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
      // oder leer setzen.
      if(!isset($templates[$template_id]))
      {
        $template_id = 0;
        if(count($templates)>0)
        $template_id = key($templates);
      }

      $AART->setTable($REX['TABLE_PREFIX'].'article');
      if (!isset ($id))
      $id = $AART->setNewId('id');
      else
      $AART->setValue('id', $id);

      $AART->setValue('clang', $key);
      $AART->setValue('template_id', $template_id);
      $AART->setValue('name', $data['name']);
      $AART->setValue('catname', $data['catname']);
      $AART->setValue('attributes', '');
      $AART->setValue('catprior', $data['catprior']);
      $AART->setValue('re_id', $category_id);
      $AART->setValue('prior', 1);
      $AART->setValue('path', $data['path']);
      $AART->setValue('startpage', 1);
      $AART->setValue('status', $data['status']);
      $AART->addGlobalUpdateFields();
      $AART->addGlobalCreateFields();

      if($AART->insert())
      {
        // ----- PRIOR
        if(isset($data['catprior']))
        {
          self::newCatPrio($category_id, $key, 0, $data['catprior']);
        }

        $message = rex_i18n::msg("category_added_and_startarticle_created");
        
        // ----- EXTENSION POINT
        // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
        $message = rex_register_extension_point('CAT_ADDED', $message,
        array (
          'category' => clone($AART),
          'id' => $id,
          're_id' => $category_id,
          'clang' => $key,
          'name' => $data['catname'],
          'prior' => $data['catprior'],
          'path' => $data['path'],
          'status' => $data['status'],
          'article' => clone($AART),
          'data' => $data,
        )
        );

      }
      else
      {
        throw new rexApiException($AART->getError());
      }
    }

    return $message;
  }

  /**
   * Bearbeitet einer Kategorie
   *
   * @param int   $category_id Id der Kategorie die verändert werden soll
   * @param int   $clang       Id der Sprache
   * @param array $data        Array mit den Daten der Kategorie
   *
   * @return string Eine Statusmeldung
   */
  static public function editCategory($category_id, $clang, $data)
  {
    global $REX;

    if(!is_array($data))
    throw new rexApiException('Expecting $data to be an array!');

    // --- Kategorie mit alten Daten selektieren
    $thisCat = rex_sql::factory();
    $thisCat->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE startpage=1 and id='.$category_id.' and clang='. $clang);

    // --- Kategorie selbst updaten
    $EKAT = rex_sql::factory();
    $EKAT->setTable($REX['TABLE_PREFIX']."article");
    $EKAT->setWhere("id=$category_id AND startpage=1 AND clang=$clang");
    $EKAT->setValue('catname', $data['catname']);
    $EKAT->setValue('catprior', $data['catprior']);
    $EKAT->addGlobalUpdateFields();

    if($EKAT->update())
    {
      // --- Kategorie Kindelemente updaten
      if(isset($data['catname']))
      {
        $ArtSql = rex_sql::factory();
        $ArtSql->setQuery('SELECT id FROM '.$REX['TABLE_PREFIX'].'article WHERE re_id='.$category_id .' AND startpage=0 AND clang='.$clang);

        $EART = rex_sql::factory();
        for($i = 0; $i < $ArtSql->getRows(); $i++)
        {
          $EART->setTable($REX['TABLE_PREFIX'].'article');
          $EART->setWhere('id='. $ArtSql->getValue('id') .' AND startpage=0 AND clang='.$clang);
          $EART->setValue('catname', $data['catname']);
          $EART->addGlobalUpdateFields();

          if($EART->update())
          {
            rex_article_cache::delete($ArtSql->getValue('id'), $clang);
          }
          else
          {
            throw new rexApiException($EART->getError());
          }

          $ArtSql->next();
        }
      }

      // ----- PRIOR
      if(isset($data['catprior']))
      {
        $re_id = $thisCat->getValue('re_id');
        $old_prio = $thisCat->getValue('catprior');

        if($data['catprior'] <= 0)
        $data['catprior'] = 1;

        self::newCatPrio($re_id, $clang, $data['catprior'], $old_prio);
      }

      $message = rex_i18n::msg('category_updated');

      rex_article_cache::delete($category_id, $clang);

      // ----- EXTENSION POINT
      // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
      $message = rex_register_extension_point('CAT_UPDATED', $message,
      array (
        'id' => $category_id,

        'category' => clone($EKAT),
        'category_old' => clone($thisCat),
        'article' => clone($EKAT),

        're_id' => $thisCat->getValue('re_id'),
        'clang' => $clang,
        'name' => $thisCat->getValue('catname'),
        'prior' => $thisCat->getValue('catprior'),
        'path' => $thisCat->getValue('path'),
        'status' => $thisCat->getValue('status'),

        'data' => $data,
      )
      );
    }
    else
    {
      throw new rexApiException($EKAT->getError());
    }

    return $message;
  }

  /**
   * Löscht eine Kategorie und reorganisiert die Prioritäten verbleibender Geschwister-Kategorien
   *
   * @param int $category_id Id der Kategorie die gelöscht werden soll
   *
   * @return string Eine Statusmeldung
   */
  static public function deleteCategory($category_id)
  {
    global $REX;

    $clang = 0;

    $thisCat = rex_sql::factory();
    $thisCat->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE id='.$category_id.' and clang='. $clang);

    // Prüfen ob die Kategorie existiert
    if ($thisCat->getRows() == 1)
    {
      $KAT = rex_sql::factory();
      $KAT->setQuery("select * from ".$REX['TABLE_PREFIX']."article where re_id='$category_id' and clang='$clang' and startpage=1");
      // Prüfen ob die Kategorie noch Unterkategorien besitzt
      if ($KAT->getRows() == 0)
      {
        $KAT->setQuery("select * from ".$REX['TABLE_PREFIX']."article where re_id='$category_id' and clang='$clang' and startpage=0");
        // Prüfen ob die Kategorie noch Artikel besitzt (ausser dem Startartikel)
        if ($KAT->getRows() == 0)
        {
          $thisCat = rex_sql::factory();
          $thisCat->setQuery('SELECT * FROM '.$REX['TABLE_PREFIX'].'article WHERE id='.$category_id);

          $re_id = $thisCat->getValue('re_id');
          $message = rex_article_service::_deleteArticle($category_id);

          foreach($thisCat as $row)
          {
            $_clang = $row->getValue('clang');

            // ----- PRIOR
            self::newCatPrio($re_id, $_clang, 0, 1);

            // ----- EXTENSION POINT
            $message = rex_register_extension_point('CAT_DELETED', $message, array (
            'id'     => $category_id,
            're_id'  => $re_id,
            'clang'  => $_clang,
            'name'   => $row->getValue('catname'),
            'prior'  => $row->getValue('catprior'),
            'path'   => $row->getValue('path'),
            'status' => $row->getValue('status'),
            ));
          }

          $users = rex_sql::factory();
          $users->setQuery('UPDATE '. $REX['TABLE_PREFIX'] .'user SET rights = REPLACE(rights, "#csw['. $category_id .']#", "#")');

        }else
        {
          throw new rexApiException(rex_i18n::msg('category_could_not_be_deleted').' '.rex_i18n::msg('category_still_contains_articles'));
        }
      }else
      {
        throw new rexApiException(rex_i18n::msg('category_could_not_be_deleted').' '.rex_i18n::msg('category_still_contains_subcategories'));
      }
    }else
    {
      throw new rexApiException(rex_i18n::msg('category_could_not_be_deleted'));
    }

    return $message;
  }

  /**
   * Ändert den Status der Kategorie
   *
   * @param int       $category_id   Id der Kategorie die gelöscht werden soll
   * @param int       $clang         Id der Sprache
   * @param int|null  $status        Status auf den die Kategorie gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
   *
   * @return array Ein Array welches den status sowie eine Fehlermeldung beinhaltet
   */
  static public function categoryStatus($category_id, $clang, $status = null)
  {
    global $REX;

    $message = '';
    $catStatusTypes = self::statusTypes();

    $KAT = rex_sql::factory();
    $KAT->setQuery("select * from ".$REX['TABLE_PREFIX']."article where id='$category_id' and clang=$clang and startpage=1");
    if ($KAT->getRows() == 1)
    {
      // Status wurde nicht von außen vorgegeben,
      // => zyklisch auf den nächsten Weiterschalten
      if(!$status)
      $newstatus = ($KAT->getValue('status') + 1) % count($catStatusTypes);
      else
      $newstatus = $status;

      $EKAT = rex_sql::factory();
      $EKAT->setTable($REX['TABLE_PREFIX'].'article');
      $EKAT->setWhere("id='$category_id' and clang=$clang and startpage=1");
      $EKAT->setValue("status", $newstatus);
      $EKAT->addGlobalCreateFields();

      if($EKAT->update())
      {
        $message = rex_i18n::msg('category_status_updated');
        rex_article_cache::delete($category_id, $clang);

        // ----- EXTENSION POINT
        $message = rex_register_extension_point('CAT_STATUS', $message, array (
        'id' => $category_id,
        'clang' => $clang,
        'status' => $newstatus
        ));
      }
      else
      {
        throw new rexApiException($EKAT->getError());
      }
    }
    else
    {
      throw new rexApiException(rex_i18n::msg("no_such_category"));
    }

    return $message;
  }

  /**
   * Gibt alle Stati zurück, die für eine Kategorie gültig sind
   *
   * @return array Array von Stati
   */
  static public function statusTypes()
  {
    global $REX;

    static $catStatusTypes;

    if(!$catStatusTypes)
    {
      $catStatusTypes = array(
      // Name, CSS-Class
      array(rex_i18n::msg('status_offline'), 'rex-offline'),
      array(rex_i18n::msg('status_online'), 'rex-online')
      );

      // ----- EXTENSION POINT
      $catStatusTypes = rex_register_extension_point('CAT_STATUS_TYPES', $catStatusTypes);
    }

    return $catStatusTypes;
  }




  /**
   * Berechnet die Prios der Kategorien in einer Kategorie neu
   *
   * @param $re_id    KategorieId der Kategorie, die erneuert werden soll
   * @param $clang    ClangId der Kategorie, die erneuert werden soll
   * @param $new_prio Neue PrioNr der Kategorie
   * @param $old_prio Alte PrioNr der Kategorie
   *
   * @return void
   */
  static public function newCatPrio($re_id, $clang, $new_prio, $old_prio)
  {
    global $REX;
    if ($new_prio != $old_prio)
    {
      if ($new_prio < $old_prio)
      $addsql = "desc";
      else
      $addsql = "asc";

      rex_organize_priorities(
      $REX['TABLE_PREFIX'].'article',
      'catprior',
      'clang='. $clang .' AND re_id='. $re_id .' AND startpage=1',
      'catprior,updatedate '. $addsql,
      'pid'
      );

      rex_article_cache::deleteLists($re_id, $clang);
    }
  }
}