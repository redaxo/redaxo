<?php

/**
 * Funktionensammlung für die Strukturverwaltung.
 *
 * @package redaxo\structure
 */
class rex_category_service
{
    /**
     * Erstellt eine neue Kategorie.
     *
     * @param int   $category_id KategorieId in der die neue Kategorie erstellt werden soll
     * @param array $data        Array mit den Daten der Kategorie
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function addCategory($category_id, array $data)
    {
        $message = '';

        self::reqKey($data, 'catpriority');
        self::reqKey($data, 'catname');

        // parent may be null, when adding in the root cat
        $parent = rex_category::get($category_id);
        if ($parent) {
            $path = $parent->getPath();
            $path .= $parent->getId() . '|';
        } else {
            $path = '|';
        }

        if ($data['catpriority'] <= 0) {
            $data['catpriority'] = 1;
        }

        if (!isset($data['name'])) {
            $data['name'] = $data['catname'];
        }

        if (!isset($data['status'])) {
            $data['status'] = 0;
        }

        $templates = [];
        $contentAvailable = rex_plugin::get('structure', 'content')->isAvailable();
        if ($contentAvailable) {
            $startpageTemplates = [];
            if ('' != $category_id) {
                // TemplateId vom Startartikel der jeweiligen Sprache vererben
                $sql = rex_sql::factory();
                // $sql->setDebug();
                $sql->setQuery('select clang_id,template_id from ' . rex::getTablePrefix() . 'article where id=? and startarticle=1', [$category_id]);
                for ($i = 0; $i < $sql->getRows(); $i++, $sql->next()) {
                    $startpageTemplates[$sql->getValue('clang_id')] = $sql->getValue('template_id');
                }
            }

            // Alle Templates der Kategorie
            $templates = rex_template::getTemplatesForCategory($category_id);
        }

        $user = self::getUser();

        // Kategorie in allen Sprachen anlegen
        $AART = rex_sql::factory();
        foreach (rex_clang::getAllIds() as $key) {
            if ($contentAvailable) {
                $template_id = rex_template::getDefaultId();
                if (isset($startpageTemplates[$key]) && '' != $startpageTemplates[$key]) {
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
            }

            if (!isset($template_id)) {
                $template_id = 0;
            }

            $AART->setTable(rex::getTablePrefix() . 'article');
            if (!isset($id)) {
                $id = $AART->setNewId('id');
            } else {
                $AART->setValue('id', $id);
            }

            $AART->setValue('clang_id', $key);
            $AART->setValue('template_id', $template_id);
            $AART->setValue('name', $data['name']);
            $AART->setValue('catname', $data['catname']);
            $AART->setValue('catpriority', $data['catpriority']);
            $AART->setValue('parent_id', $category_id);
            $AART->setValue('priority', 1);
            $AART->setValue('path', $path);
            $AART->setValue('startarticle', 1);
            $AART->setValue('status', $data['status']);
            $AART->addGlobalUpdateFields($user);
            $AART->addGlobalCreateFields($user);

            try {
                $AART->insert();

                // ----- PRIOR
                if (isset($data['catpriority'])) {
                    self::newCatPrio($category_id, $key, 0, $data['catpriority']);
                }

                $message = rex_i18n::msg('category_added_and_startarticle_created');

                rex_article_cache::delete($id, $key);

                // ----- EXTENSION POINT
                // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
                $message = rex_extension::registerPoint(new rex_extension_point('CAT_ADDED', $message, [
                    'category' => clone $AART,
                    'id' => $id,
                    'parent_id' => $category_id,
                    'clang' => $key,
                    'name' => $data['catname'],
                    'priority' => $data['catpriority'],
                    'path' => $path,
                    'status' => $data['status'],
                    'article' => clone $AART,
                    'data' => $data,
                ]));
            } catch (rex_sql_exception $e) {
                throw new rex_api_exception($e->getMessage(), $e);
            }
        }

        return $message;
    }

    /**
     * Bearbeitet einer Kategorie.
     *
     * @param int   $category_id Id der Kategorie die verändert werden soll
     * @param int   $clang       Id der Sprache
     * @param array $data        Array mit den Daten der Kategorie
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function editCategory($category_id, $clang, array $data)
    {
        // --- Kategorie mit alten Daten selektieren
        $thisCat = rex_sql::factory();
        $thisCat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE startarticle=1 and id=? and clang_id=?', [$category_id, $clang]);

        // --- Kategorie selbst updaten
        $EKAT = rex_sql::factory();
        $EKAT->setTable(rex::getTablePrefix() . 'article');
        $EKAT->setWhere(['id' => $category_id, 'startarticle' => 1, 'clang_id' => $clang]);

        if (isset($data['catname'])) {
            $EKAT->setValue('catname', $data['catname']);
        }
        if (isset($data['catpriority'])) {
            $EKAT->setValue('catpriority', $data['catpriority']);
        }

        $user = self::getUser();

        $EKAT->addGlobalUpdateFields($user);

        try {
            $EKAT->update();

            // --- Kategorie Kindelemente updaten
            if (isset($data['catname'])) {
                $ArtSql = rex_sql::factory();
                $ArtSql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=? AND startarticle=0 AND clang_id=?', [$category_id, $clang]);

                $EART = rex_sql::factory();
                for ($i = 0; $i < $ArtSql->getRows(); ++$i) {
                    $EART->setTable(rex::getTablePrefix() . 'article');
                    $EART->setWhere(['id' => $ArtSql->getValue('id'), 'startarticle' => '0', 'clang_id' => $clang]);
                    $EART->setValue('catname', $data['catname']);
                    $EART->addGlobalUpdateFields($user);

                    $EART->update();
                    rex_article_cache::delete($ArtSql->getValue('id'), $clang);

                    $ArtSql->next();
                }
            }

            // ----- PRIOR
            if (isset($data['catpriority'])) {
                $parent_id = $thisCat->getValue('parent_id');
                $old_prio = $thisCat->getValue('catpriority');

                if ($data['catpriority'] <= 0) {
                    $data['catpriority'] = 1;
                }

                if ($old_prio != $data['catpriority']) {
                    rex_sql::factory()
                        ->setTable(rex::getTable('article'))
                        ->setWhere('id = :id AND clang_id != :clang', ['id' => $category_id, 'clang' => $clang])
                        ->setValue('catpriority', $data['catpriority'])
                        ->addGlobalUpdateFields($user)
                        ->update();

                    foreach (rex_clang::getAllIds() as $clangId) {
                        self::newCatPrio($parent_id, $clangId, $data['catpriority'], $old_prio);
                    }
                }
            }

            $message = rex_i18n::msg('category_updated');

            rex_article_cache::delete($category_id);

            // ----- EXTENSION POINT
            // Objekte clonen, damit diese nicht von der extension veraendert werden koennen
            $message = rex_extension::registerPoint(new rex_extension_point('CAT_UPDATED', $message, [
                'id' => $category_id,

                'category' => clone $EKAT,
                'category_old' => clone $thisCat,
                'article' => clone $EKAT,

                'parent_id' => $thisCat->getValue('parent_id'),
                'clang' => $clang,
                'name' => $data['catname'] ?? $thisCat->getValue('catname'),
                'priority' => $data['catpriority'] ?? $thisCat->getValue('catpriority'),
                'path' => $thisCat->getValue('path'),
                'status' => $thisCat->getValue('status'),

                'data' => $data,
            ]));
        } catch (rex_sql_exception $e) {
            throw new rex_api_exception($e->getMessage(), $e);
        }

        return $message;
    }

    /**
     * Löscht eine Kategorie und reorganisiert die Prioritäten verbleibender Geschwister-Kategorien.
     *
     * @param int $category_id Id der Kategorie die gelöscht werden soll
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function deleteCategory($category_id)
    {
        $clang = 1;

        $thisCat = rex_sql::factory();
        $thisCat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=? and clang_id=?', [$category_id, $clang]);

        // Prüfen ob die Kategorie existiert
        if (1 == $thisCat->getRows()) {
            $KAT = rex_sql::factory();
            $KAT->setQuery('select * from ' . rex::getTablePrefix() . 'article where parent_id=? and clang_id=? and startarticle=1', [$category_id, $clang]);
            // Prüfen ob die Kategorie noch Unterkategorien besitzt
            if (0 == $KAT->getRows()) {
                $KAT->setQuery('select * from ' . rex::getTablePrefix() . 'article where parent_id=? and clang_id=? and startarticle=0', [$category_id, $clang]);
                // Prüfen ob die Kategorie noch Artikel besitzt (ausser dem Startartikel)
                if (0 == $KAT->getRows()) {
                    $thisCat = rex_sql::factory();
                    $thisCat->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=?', [$category_id]);

                    $parent_id = $thisCat->getValue('parent_id');
                    $message = rex_article_service::_deleteArticle($category_id);

                    foreach ($thisCat as $row) {
                        $_clang = $row->getValue('clang_id');

                        // ----- PRIOR
                        self::newCatPrio($parent_id, $_clang, 0, 1);

                        // ----- EXTENSION POINT
                        $message = rex_extension::registerPoint(new rex_extension_point('CAT_DELETED', $message, [
                            'id' => $category_id,
                            'parent_id' => $parent_id,
                            'clang' => $_clang,
                            'name' => $row->getValue('catname'),
                            'priority' => $row->getValue('catpriority'),
                            'path' => $row->getValue('path'),
                            'status' => $row->getValue('status'),
                        ]));
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
     * Ändert den Status der Kategorie.
     *
     * @param int      $category_id Id der Kategorie die gelöscht werden soll
     * @param int      $clang       Id der Sprache
     * @param int|null $status      Status auf den die Kategorie gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
     *
     * @throws rex_api_exception
     *
     * @return int Der neue Status der Kategorie
     */
    public static function categoryStatus($category_id, $clang, $status = null)
    {
        $KAT = rex_sql::factory();
        $KAT->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and clang_id=? and startarticle=1', [$category_id, $clang]);
        if (1 == $KAT->getRows()) {
            // Status wurde nicht von außen vorgegeben,
            // => zyklisch auf den nächsten Weiterschalten
            if (null === $status) {
                $newstatus = self::nextStatus($KAT->getValue('status'));
            } else {
                $newstatus = $status;
            }

            $EKAT = rex_sql::factory();
            $EKAT->setTable(rex::getTablePrefix() . 'article');
            $EKAT->setWhere(['id' => $category_id,  'clang_id' => $clang, 'startarticle' => 1]);
            $EKAT->setValue('status', $newstatus);
            $EKAT->addGlobalCreateFields(self::getUser());

            try {
                $EKAT->update();

                rex_article_cache::delete($category_id, $clang);

                // ----- EXTENSION POINT
                rex_extension::registerPoint(new rex_extension_point('CAT_STATUS', null, [
                    'id' => $category_id,
                    'clang' => $clang,
                    'status' => $newstatus,
                ]));
            } catch (rex_sql_exception $e) {
                throw new rex_api_exception($e->getMessage(), $e);
            }
        } else {
            throw new rex_api_exception(rex_i18n::msg('no_such_category'));
        }

        return $newstatus;
    }

    /**
     * Gibt alle Stati zurück, die für eine Kategorie gültig sind.
     *
     * @return array Array von Stati
     */
    public static function statusTypes()
    {
        static $catStatusTypes;

        if (!$catStatusTypes) {
            $catStatusTypes = [
                // Name, CSS-Class, Icon
                [rex_i18n::msg('status_offline'), 'rex-offline', 'rex-icon-offline'],
                [rex_i18n::msg('status_online'), 'rex-online', 'rex-icon-online'],
            ];

            // ----- EXTENSION POINT
            $catStatusTypes = rex_extension::registerPoint(new rex_extension_point('CAT_STATUS_TYPES', $catStatusTypes));
        }

        return $catStatusTypes;
    }

    public static function nextStatus($currentStatus)
    {
        $catStatusTypes = self::statusTypes();
        return ($currentStatus + 1) % count($catStatusTypes);
    }

    public static function prevStatus($currentStatus)
    {
        $catStatusTypes = self::statusTypes();
        if (($currentStatus - 1) < 0) {
            return count($catStatusTypes) - 1;
        }

        return ($currentStatus - 1) % count($catStatusTypes);
    }

    /**
     * Kopiert eine Kategorie in eine andere.
     *
     * @param int $from_cat KategorieId der Kategorie, die kopiert werden soll (Quelle)
     * @param int $to_cat   KategorieId der Kategorie, IN die kopiert werden soll (Ziel)
     */
    public static function copyCategory($from_cat, $to_cat)
    {
        // TODO rex_copyCategory implementieren
    }

    /**
     * Berechnet die Prios der Kategorien in einer Kategorie neu.
     *
     * @param int $parent_id KategorieId der Kategorie, die erneuert werden soll
     * @param int $clang     ClangId der Kategorie, die erneuert werden soll
     * @param int $new_prio  Neue PrioNr der Kategorie
     * @param int $old_prio  Alte PrioNr der Kategorie
     */
    public static function newCatPrio($parent_id, $clang, $new_prio, $old_prio)
    {
        if ($new_prio != $old_prio) {
            if ($new_prio < $old_prio) {
                $addsql = 'desc';
            } else {
                $addsql = 'asc';
            }

            rex_sql_util::organizePriorities(
                rex::getTable('article'),
                'catpriority',
                'clang_id=' . (int) $clang . ' AND parent_id=' . (int) $parent_id . ' AND startarticle=1',
                'catpriority,updatedate ' . $addsql
            );

            rex_article_cache::deleteLists($parent_id);
            rex_article_cache::deleteMeta($parent_id);

            $ids = rex_sql::factory()->getArray('SELECT id FROM '.rex::getTable('article').' WHERE startarticle=1 AND parent_id = ? GROUP BY id', [$parent_id]);
            foreach ($ids as $id) {
                rex_article_cache::deleteMeta($id['id']);
            }
        }
    }

    /**
     * Verschieben einer Kategorie in eine andere.
     *
     * @param int $from_cat KategorieId der Kategorie, die verschoben werden soll (Quelle)
     * @param int $to_cat   KategorieId der Kategorie, IN die verschoben werden soll (Ziel)
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function moveCategory($from_cat, $to_cat)
    {
        $from_cat = (int) $from_cat;
        $to_cat = (int) $to_cat;

        if ($from_cat == $to_cat) {
            // kann nicht in gleiche kategroie kopiert werden
            return false;
        }

        // kategorien vorhanden ?
        // ist die zielkategorie im pfad der quellkategeorie ?
        $fcat = rex_sql::factory();
        $fcat->setQuery('select * from ' . rex::getTablePrefix() . 'article where startarticle=1 and id=? and clang_id=?', [$from_cat, rex_clang::getStartId()]);

        $tcat = rex_sql::factory();
        $tcat->setQuery('select * from ' . rex::getTablePrefix() . 'article where startarticle=1 and id=? and clang_id=?', [$to_cat, rex_clang::getStartId()]);

        if (1 != $fcat->getRows() || (1 != $tcat->getRows() && 0 != $to_cat)) {
            // eine der kategorien existiert nicht
            return false;
        }
        if ($to_cat > 0) {
            $tcats = explode('|', $tcat->getValue('path'));
            if (in_array($from_cat, $tcats)) {
                // zielkategorie ist in quellkategorie -> nicht verschiebbar
                return false;
            }
        }

        // ----- folgende cats regenerate
        $RC = [];
        $RC[$fcat->getValue('parent_id')] = 1;
        $RC[$from_cat] = 1;
        $RC[$to_cat] = 1;

        if ($to_cat > 0) {
            $to_path = $tcat->getValue('path') . $to_cat . '|';
        } else {
            $to_path = '|';
        }

        $from_path = $fcat->getValue('path') . $from_cat . '|';

        $gcats = rex_sql::factory();
        // $gcats->setDebug();
        $gcats->setQuery('select * from ' . rex::getTablePrefix() . 'article where path like ? and clang_id=?', [$from_path . '%', rex_clang::getStartId()]);

        $up = rex_sql::factory();
        // $up->setDebug();
        for ($i = 0; $i < $gcats->getRows(); ++$i) {
            // make update
            $new_path = $to_path . $from_cat . '|' . str_replace($from_path, '', $gcats->getValue('path'));
            $icid = $gcats->getValue('id');

            // path aendern und speichern
            $up->setTable(rex::getTablePrefix() . 'article');
            $up->setWhere(['id' => $icid]);
            $up->setValue('path', $new_path);
            $up->update();

            // cat in gen eintragen
            $RC[$icid] = 1;

            $gcats->next();
        }

        // ----- clang holen, max catprio holen und entsprechen updaten
        $gmax = rex_sql::factory();
        $up = rex_sql::factory();
        // $up->setDebug();
        foreach (rex_clang::getAllIds() as $clang) {
            $gmax->setQuery('select max(catpriority) from ' . rex::getTablePrefix() . 'article where parent_id=? and clang_id=?', [$to_cat, $clang]);
            $catpriority = (int) $gmax->getValue('max(catpriority)');
            $up->setTable(rex::getTablePrefix() . 'article');
            $up->setWhere(['id' => $from_cat, 'clang_id' => $clang]);
            $up->setValue('path', $to_path);
            $up->setValue('parent_id', $to_cat);
            $up->setValue('catpriority', ($catpriority + 1));
            $up->update();
        }

        // ----- generiere artikel neu - ohne neue inhaltsgenerierung
        foreach ($RC as $id => $key) {
            rex_article_cache::delete($id);
        }

        foreach (rex_clang::getAllIds() as $clang) {
            self::newCatPrio($fcat->getValue('parent_id'), $clang, 0, 1);

            rex_extension::registerPoint(new rex_extension_point('CAT_MOVED', null, [
                'id' => $from_cat,
                'clang_id' => $clang, // deprecated, use "clang" instead
                'clang' => $clang,
                'category_id' => $to_cat,
            ]));
        }

        return true;
    }

    /**
     * Checks whether the required array key $keyName isset.
     *
     * @param array  $array   The array
     * @param string $keyName The key
     *
     * @throws rex_api_exception
     */
    protected static function reqKey(array $array, $keyName)
    {
        if (!isset($array[$keyName])) {
            throw new rex_api_exception('Missing required parameter "' . $keyName . '"!');
        }
    }

    /**
     * @return string
     */
    private static function getUser()
    {
        if (rex::getUser()) {
            return rex::getUser()->getLogin();
        }

        return rex::getEnvironment();
    }
}
