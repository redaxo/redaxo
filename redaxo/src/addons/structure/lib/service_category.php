<?php

/**
 * Funktionensammlung für die Strukturverwaltung
 *
 * @package redaxo5
 */
class rex_category_service
{
  /**
   * Erstellt eine neue Kategorie
   *
   * @param int   $category_id KategorieId in der die neue Kategorie erstellt werden soll
   * @param array $data        Array mit den Daten der Kategorie
   *
   * @return string Eine Statusmeldung
   */
  static public function addCategory($category_id, array $data)
  {
    $message = '';

    if (!is_array($data)) {
      throw  new rex_api_exception('Expecting $data to be an array!');
    }

    self::reqKey($data, 'catprior');
    self::reqKey($data, 'catname');

    $startpageTemplates = array();
    if ($category_id != '') {
      // TemplateId vom Startartikel der jeweiligen Sprache vererben
      $sql = rex_sql::factory();
      // $sql->debugsql = 1;
      $sql->setQuery('select clang,template_id from ' . rex::getTablePrefix() . "article where id=$category_id and startpage=1");
      for ($i = 0; $i < $sql->getRows(); $i++, $sql->next()) {
        $startpageTemplates[$sql->getValue('clang')] = $sql->getValue('template_id');
      }
    }

    // parent may be null, when adding in the root cat
    $parent = rex_category::getCategoryById($category_id);
    if ($parent) {
      $path = $parent->getPath();
      $path .= $parent->getId() . '|';
    } else {
      $path = '|';
    }

    if ($data['catprior'] <= 0) {
      $data['catprior'] = 1;
    }

    if (!isset($data['name'])) {
      $data['name'] = $data['catname'];
    }

    if (!isset($data['status'])) {
      $data['status'] = 0;
    }

    // Alle Templates der Kategorie
    $templates = rex_category::getTemplates($category_id);
    // Kategorie in allen Sprachen anlegen
    $AART = rex_sql::factory();
    foreach (rex_clang::getAllIds() as $key) {
      $template_id = rex::getProperty('default_template_id');
      if (isset ($startpageTemplates[$key]) && $startpageTemplates[$key] != '') {
        $template_id = $startpageTemplates[$key];
      }

      // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
      // oder leer setzen.
      if (!isset($templates[$template_id])) {
        $template_id = 0;
        if (count($templates) > 0) {
          $template_id = key($templates);
        }
      }

      $AART->setTable(rex::getTablePrefix() . 'article');
      if (!isset ($id)) {
        $id = $AART->setNewId('id');
      } else {
        $AART->setValue('id', $id);
      }

      $AART->setValue('clang', $key);
      $AART->setValue('template_id', $template_id);
      $AART->setValue('name', $data['name']);
      $AART->setValue('catname', $data['catname']);
      $AART->setValue('attributes', '');
      $AART->setValue('catprior', $data['catprior']);
      $AART->setValue('re_id', $category_id);
      $AART->setValue('prior', 1);
      $AART->setValue('path', $path);
      $AART->setValue('startpage', 1);
      $AART->setValue('status', $data['status']);
      $AART->addGlobalUpdateFields();
      $AART->addGlobalCreateFields();

      try {
        $AART->insert();

        // ----- PRIOR
        if (isset($data['catprior'])) {
          self::newCatPrio($category_id, $key, 0, $data['catprior']);
        }

        $message = rex_i18n::msg('category_added_and_startarticle_created');

        // ----- EXTENSION POINT
        // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
        $message = rex_extension::registerPoint('CAT_ADDED', $message,
        array (
          'category' => clone $AART,
          'id' => $id,
          're_id' => $category_id,
          'clang' => $key,
          'name' => $data['catname'],
          'prior' => $data['catprior'],
          'path' => $path,
          'status' => $data['status'],
          'article' => clone $AART,
          'data' => $data,
        ));

      } catch (rex_sql_exception $e) {
        throw new rex_api_exception($e);
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
  static public function editCategory($category_id, $clang, array $data)
  {
    $message = '';

    if (!is_array($data)) {
      throw  new rex_api_exception('Expecting $data to be an array!');
    }

    // --- Kategorie mit alten Daten selektieren
    $thisCat = rex_sql::factory();
    $thisCat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE startpage=1 and id=' . $category_id . ' and clang=' . $clang);

    // --- Kategorie selbst updaten
    $EKAT = rex_sql::factory();
    $EKAT->setTable(rex::getTablePrefix() . 'article');
    $EKAT->setWhere(array('id' => $category_id, 'startpage' => 1, 'clang' => $clang));

    if (isset($data['catname'])) {
      $EKAT->setValue('catname', $data['catname']);
    }
    if (isset($data['catprior'])) {
      $EKAT->setValue('catprior', $data['catprior']);
    }

    $EKAT->addGlobalUpdateFields();

    try {
      $EKAT->update();

      // --- Kategorie Kindelemente updaten
      if (isset($data['catname'])) {
        $ArtSql = rex_sql::factory();
        $ArtSql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article WHERE re_id=' . $category_id . ' AND startpage=0 AND clang=' . $clang);

        $EART = rex_sql::factory();
        for ($i = 0; $i < $ArtSql->getRows(); $i++) {
          $EART->setTable(rex::getTablePrefix() . 'article');
          $EART->setWhere(array( 'id' => $ArtSql->getValue('id'), 'startpage' => '0', 'clang' => $clang));
          $EART->setValue('catname', $data['catname']);
          $EART->addGlobalUpdateFields();

          $EART->update();
          rex_article_cache::delete($ArtSql->getValue('id'), $clang);

          $ArtSql->next();
        }
      }

      // ----- PRIOR
      if (isset($data['catprior'])) {
        $re_id = $thisCat->getValue('re_id');
        $old_prio = $thisCat->getValue('catprior');

        if ($data['catprior'] <= 0)
        $data['catprior'] = 1;

        self::newCatPrio($re_id, $clang, $data['catprior'], $old_prio);
      }

      $message = rex_i18n::msg('category_updated');

      rex_article_cache::delete($category_id, $clang);

      // ----- EXTENSION POINT
      // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
      $message = rex_extension::registerPoint('CAT_UPDATED', $message,
        array (
          'id' => $category_id,

          'category' => clone $EKAT,
          'category_old' => clone $thisCat,
          'article' => clone $EKAT,

          're_id' => $thisCat->getValue('re_id'),
          'clang' => $clang,
          'name' => $thisCat->getValue('catname'),
          'prior' => $thisCat->getValue('catprior'),
          'path' => $thisCat->getValue('path'),
          'status' => $thisCat->getValue('status'),

          'data' => $data,
        )
      );
    } catch (rex_sql_exception $e) {
      throw new rex_api_exception($e);
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
    $clang = 0;

    $thisCat = rex_sql::factory();
    $thisCat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=' . $category_id . ' and clang=' . $clang);

    // Prüfen ob die Kategorie existiert
    if ($thisCat->getRows() == 1) {
      $KAT = rex_sql::factory();
      $KAT->setQuery('select * from ' . rex::getTablePrefix() . "article where re_id='$category_id' and clang='$clang' and startpage=1");
      // Prüfen ob die Kategorie noch Unterkategorien besitzt
      if ($KAT->getRows() == 0) {
        $KAT->setQuery('select * from ' . rex::getTablePrefix() . "article where re_id='$category_id' and clang='$clang' and startpage=0");
        // Prüfen ob die Kategorie noch Artikel besitzt (ausser dem Startartikel)
        if ($KAT->getRows() == 0) {
          $thisCat = rex_sql::factory();
          $thisCat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=' . $category_id);

          $re_id = $thisCat->getValue('re_id');
          $message = rex_article_service::_deleteArticle($category_id);

          foreach ($thisCat as $row) {
            $_clang = $row->getValue('clang');

            // ----- PRIOR
            self::newCatPrio($re_id, $_clang, 0, 1);

            // ----- EXTENSION POINT
            $message = rex_extension::registerPoint('CAT_DELETED', $message, array (
            'id'     => $category_id,
            're_id'  => $re_id,
            'clang'  => $_clang,
            'name'   => $row->getValue('catname'),
            'prior'  => $row->getValue('catprior'),
            'path'   => $row->getValue('path'),
            'status' => $row->getValue('status'),
            ));
          }

          rex_complex_perm::removeItem('structure', $category_id);

        } else {
          throw new rex_api_exception(rex_i18n::msg('category_could_not_be_deleted') . ' ' . rex_i18n::msg('category_still_contains_articles'));
        }
      } else {
        throw new rex_api_exception(rex_i18n::msg('category_could_not_be_deleted') . ' ' . rex_i18n::msg('category_still_contains_subcategories'));
      }
    } else {
      throw new rex_api_exception(rex_i18n::msg('category_could_not_be_deleted'));
    }

    return $message;
  }

  /**
   * Ändert den Status der Kategorie
   *
   * @param int      $category_id Id der Kategorie die gelöscht werden soll
   * @param int      $clang       Id der Sprache
   * @param int|null $status      Status auf den die Kategorie gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
   *
   * @return int Der neue Status der Kategorie
   */
  static public function categoryStatus($category_id, $clang, $status = null)
  {
    $message = '';
    $catStatusTypes = self::statusTypes();

    $KAT = rex_sql::factory();
    $KAT->setQuery('select * from ' . rex::getTablePrefix() . "article where id='$category_id' and clang=$clang and startpage=1");
    if ($KAT->getRows() == 1) {
      // Status wurde nicht von außen vorgegeben,
      // => zyklisch auf den nächsten Weiterschalten
      if (!$status)
      $newstatus = self::nextStatus($KAT->getValue('status'));
      else
      $newstatus = $status;

      $EKAT = rex_sql::factory();
      $EKAT->setTable(rex::getTablePrefix() . 'article');
      $EKAT->setWhere(array('id' => $category_id,  'clang' => $clang, 'startpage' => 1));
      $EKAT->setValue('status', $newstatus);
      $EKAT->addGlobalCreateFields();

      try {
        $EKAT->update();

        rex_article_cache::delete($category_id, $clang);

        // ----- EXTENSION POINT
        rex_extension::registerPoint('CAT_STATUS', null, array (
          'id' => $category_id,
          'clang' => $clang,
          'status' => $newstatus
        ));
      } catch (rex_sql_exception $e) {
        throw new rex_api_exception($e);
      }
    } else {
      throw new rex_api_exception(rex_i18n::msg('no_such_category'));
    }

    return $newstatus;
  }

  /**
   * Gibt alle Stati zurück, die für eine Kategorie gültig sind
   *
   * @return array Array von Stati
   */
  static public function statusTypes()
  {
    static $catStatusTypes;

    if (!$catStatusTypes) {
      $catStatusTypes = array(
      // Name, CSS-Class
      array(rex_i18n::msg('status_offline'), 'rex-offline'),
      array(rex_i18n::msg('status_online'), 'rex-online')
      );

      // ----- EXTENSION POINT
      $catStatusTypes = rex_extension::registerPoint('CAT_STATUS_TYPES', $catStatusTypes);
    }

    return $catStatusTypes;
  }

  static public function nextStatus($currentStatus)
  {
    $catStatusTypes = self::statusTypes();
    return ($currentStatus + 1) % count($catStatusTypes);
  }

  static public function prevStatus($currentStatus)
  {
    $catStatusTypes = self::statusTypes();
    if (($currentStatus - 1) < 0 ) return count($catStatusTypes) - 1;

    return ($currentStatus - 1) % count($catStatusTypes);
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
    if ($new_prio != $old_prio) {
      if ($new_prio < $old_prio)
        $addsql = 'desc';
      else
        $addsql = 'asc';

      rex_sql_util::organizePriorities(
        rex::getTable('article'),
        'catprior',
        'clang=' . $clang . ' AND re_id=' . $re_id . ' AND startpage=1',
        'catprior,updatedate ' . $addsql,
        'pid'
      );

      rex_article_cache::deleteLists($re_id, $clang);
    }
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

    if ($from_cat == $to_cat) {
      // kann nicht in gleiche kategroie kopiert werden
      return false;
    } else {
      // kategorien vorhanden ?
      // ist die zielkategorie im pfad der quellkategeorie ?
      $fcat = rex_sql::factory();
      $fcat->setQuery('select * from ' . rex::getTablePrefix() . "article where startpage=1 and id=$from_cat and clang=0");

      $tcat = rex_sql::factory();
      $tcat->setQuery('select * from ' . rex::getTablePrefix() . "article where startpage=1 and id=$to_cat and clang=0");

      if ($fcat->getRows() != 1 or ($tcat->getRows() != 1 && $to_cat != 0)) {
        // eine der kategorien existiert nicht
        return false;
      } else {
        if ($to_cat > 0) {
          $tcats = explode('|', $tcat->getValue('path'));
          if (in_array($from_cat, $tcats)) {
            // zielkategorie ist in quellkategorie -> nicht verschiebbar
            return false;
          }
        }

        // ----- folgende cats regenerate
        $RC = array();
        $RC[$fcat->getValue('re_id')] = 1;
        $RC[$from_cat] = 1;
        $RC[$to_cat] = 1;

        if ($to_cat > 0) {
          $to_path = $tcat->getValue('path') . $to_cat . '|';
          $to_re_id = $tcat->getValue('re_id');
        } else {
          $to_path = '|';
          $to_re_id = 0;
        }

        $from_path = $fcat->getValue('path') . $from_cat . '|';

        $gcats = rex_sql::factory();
        // $gcats->debugsql = 1;
        $gcats->setQuery('select * from ' . rex::getTablePrefix() . "article where path like '" . $from_path . "%' and clang=0");

        $up = rex_sql::factory();
        // $up->debugsql = 1;
        for ($i = 0; $i < $gcats->getRows(); $i++) {
          // make update
          $new_path = $to_path . $from_cat . '|' . str_replace($from_path, '', $gcats->getValue('path'));
          $icid = $gcats->getValue('id');
          $irecid = $gcats->getValue('re_id');

          // path aendern und speichern
          $up->setTable(rex::getTablePrefix() . 'article');
          $up->setWhere("id=$icid");
          $up->setValue('path', $new_path);
          $up->update();

          // cat in gen eintragen
          $RC[$icid] = 1;

          $gcats->next();
        }

        // ----- clang holen, max catprio holen und entsprechen updaten
        $gmax = rex_sql::factory();
        $up = rex_sql::factory();
        // $up->debugsql = 1;
        foreach (rex_clang::getAllIds() as $clang) {
          $gmax->setQuery('select max(catprior) from ' . rex::getTablePrefix() . "article where re_id=$to_cat and clang=" . $clang);
          $catprior = (int) $gmax->getValue('max(catprior)');
          $up->setTable(rex::getTablePrefix() . 'article');
          $up->setWhere("id=$from_cat and clang=$clang ");
          $up->setValue('path', $to_path);
          $up->setValue('re_id', $to_cat);
          $up->setValue('catprior', ($catprior + 1));
          $up->update();
        }

        // ----- generiere artikel neu - ohne neue inhaltsgenerierung
        foreach ($RC as $id => $key) {
          rex_article_cache::delete($id);
        }

        foreach (rex_clang::getAllIds() as $clang) {
          self::newCatPrio($fcat->getValue('re_id'), $clang, 0, 1);
        }
      }
    }

    return true;
  }

  /**
   * Checks whether the required array key $keyName isset
   *
   * @param array  $array   The array
   * @param string $keyName The key
   */
  static protected function reqKey(array $array, $keyName)
  {
    if (!isset($array[$keyName])) {
      throw new rex_api_exception('Missing required parameter "' . $keyName . '"!');
    }
  }
}
