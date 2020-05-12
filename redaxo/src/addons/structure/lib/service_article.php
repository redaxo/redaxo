<?php

/**
 * @package redaxo\structure
 */
class rex_article_service
{
    /**
     * Erstellt einen neuen Artikel.
     *
     * @param array $data Array mit den Daten des Artikels
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function addArticle($data)
    {
        if (!is_array($data)) {
            throw new rex_api_exception('Expecting $data to be an array!');
        }

        self::reqKey($data, 'category_id');
        self::reqKey($data, 'priority');
        self::reqKey($data, 'name');

        if ($data['priority'] <= 0) {
            $data['priority'] = 1;
        }

        // parent may be null, when adding in the root cat
        $parent = rex_category::get($data['category_id']);
        if ($parent) {
            $path = $parent->getPath();
            $path .= $parent->getId() . '|';
        } else {
            $path = '|';
        }

        if (rex_plugin::get('structure', 'content')->isAvailable()) {
            $templates = rex_template::getTemplatesForCategory($data['category_id']);

            // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
            // oder leer setzen.
            if (!isset($templates[$data['template_id']])) {
                $data['template_id'] = 0;
                if (count($templates) > 0) {
                    $data['template_id'] = key($templates);
                }
            }
        }

        $message = rex_i18n::msg('article_added');

        $AART = rex_sql::factory();
        $user = self::getUser();
        foreach (rex_clang::getAllIds() as $key) {
            // ------- Kategorienamen holen
            $category = rex_category::get($data['category_id'], $key);

            $categoryName = '';
            if ($category) {
                $categoryName = $category->getName();
            }

            $AART->setTable(rex::getTablePrefix() . 'article');
            if (!isset($id) || !$id) {
                $id = $AART->setNewId('id');
            } else {
                $AART->setValue('id', $id);
            }
            $AART->setValue('name', $data['name']);
            $AART->setValue('catname', $categoryName);
            $AART->setValue('clang_id', $key);
            $AART->setValue('parent_id', $data['category_id']);
            $AART->setValue('priority', $data['priority']);
            $AART->setValue('path', $path);
            $AART->setValue('startarticle', 0);
            $AART->setValue('status', 0);
            $AART->setValue('template_id', $data['template_id']);
            $AART->addGlobalCreateFields($user);
            $AART->addGlobalUpdateFields($user);

            try {
                $AART->insert();
                // ----- PRIOR
                self::newArtPrio($data['category_id'], $key, 0, $data['priority']);
            } catch (rex_sql_exception $e) {
                throw new rex_api_exception($e->getMessage(), $e);
            }

            rex_article_cache::delete($id, $key);

            // ----- EXTENSION POINT
            $message = rex_extension::registerPoint(new rex_extension_point('ART_ADDED', $message, [
                'id' => $id,
                'clang' => $key,
                'status' => 0,
                'name' => $data['name'],
                'parent_id' => $data['category_id'],
                'priority' => $data['priority'],
                'path' => $path,
                'template_id' => $data['template_id'],
                'data' => $data,
            ]));
        }

        return $message;
    }

    /**
     * Bearbeitet einen Artikel.
     *
     * @param int   $article_id Id des Artikels der verändert werden soll
     * @param int   $clang      Id der Sprache
     * @param array $data       Array mit den Daten des Artikels
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function editArticle($article_id, $clang, $data)
    {
        if (!is_array($data)) {
            throw  new rex_api_exception('Expecting $data to be an array!');
        }

        self::reqKey($data, 'name');

        // Artikel mit alten Daten selektieren
        $thisArt = rex_sql::factory();
        $thisArt->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and clang_id=?', [$article_id, $clang]);

        if (1 != $thisArt->getRows()) {
            throw new rex_api_exception('Unable to find article with id "' . $article_id . '" and clang "' . $clang . '"!');
        }

        $ooArt = rex_article::get($article_id, $clang);
        $data['category_id'] = $ooArt->getCategoryId();

        if (rex_plugin::get('structure', 'content')->isAvailable()) {
            $templates = rex_template::getTemplatesForCategory($data['category_id']);

            // Wenn Template nicht vorhanden, dann entweder erlaubtes nehmen
            // oder leer setzen.
            if (!isset($templates[$data['template_id']])) {
                $data['template_id'] = 0;
                if (count($templates) > 0) {
                    $data['template_id'] = key($templates);
                }
            }
        }

        if (isset($data['priority'])) {
            if ($data['priority'] <= 0) {
                $data['priority'] = 1;
            }
        }

        // complete remaining optional aprams
        foreach (['path', 'priority'] as $optionalData) {
            if (!isset($data[$optionalData])) {
                $data[$optionalData] = $thisArt->getValue($optionalData);
            }
        }

        $EA = rex_sql::factory();
        $EA->setTable(rex::getTablePrefix() . 'article');
        $EA->setWhere(['id' => $article_id, 'clang_id' => $clang]);
        $EA->setValue('name', $data['name']);
        $EA->setValue('template_id', $data['template_id']);
        $EA->setValue('priority', $data['priority']);
        $EA->addGlobalUpdateFields(self::getUser());

        try {
            $EA->update();
            $message = rex_i18n::msg('article_updated');

            // ----- PRIOR
            $oldPrio = $thisArt->getValue('priority');

            if ($oldPrio != $data['priority']) {
                rex_sql::factory()
                    ->setTable(rex::getTable('article'))
                    ->setWhere('id = :id AND clang_id != :clang', ['id' => $article_id, 'clang' => $clang])
                    ->setValue('priority', $data['priority'])
                    ->addGlobalUpdateFields(self::getUser())
                    ->update();

                foreach (rex_clang::getAllIds() as $clangId) {
                    self::newArtPrio($data['category_id'], $clangId, $data['priority'], $oldPrio);
                }
            }

            rex_article_cache::delete($article_id);

            // ----- EXTENSION POINT
            $message = rex_extension::registerPoint(new rex_extension_point('ART_UPDATED', $message, [
                'id' => $article_id,
                'article' => clone $EA,
                'article_old' => clone $thisArt,
                'status' => $thisArt->getValue('status'),
                'name' => $data['name'],
                'clang' => $clang,
                'parent_id' => $data['category_id'],
                'priority' => $data['priority'],
                'path' => $data['path'],
                'template_id' => $data['template_id'],
                'data' => $data,
            ]));
        } catch (rex_sql_exception $e) {
            throw new rex_api_exception($e->getMessage(), $e);
        }

        return $message;
    }

    /**
     * Löscht einen Artikel und reorganisiert die Prioritäten verbleibender Geschwister-Artikel.
     *
     * @param int $article_id Id des Artikels die gelöscht werden soll
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function deleteArticle($article_id)
    {
        $Art = rex_sql::factory();
        $Art->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and startarticle=0', [$article_id]);

        if ($Art->getRows() > 0) {
            $message = self::_deleteArticle($article_id);
            $parent_id = $Art->getValue('parent_id');

            foreach (rex_clang::getAllIds() as $clang) {
                // ----- PRIOR
                self::newArtPrio($Art->getValue('parent_id'), $clang, 0, 1);

                // ----- EXTENSION POINT
                $message = rex_extension::registerPoint(new rex_extension_point('ART_DELETED', $message, [
                    'id' => $article_id,
                    'clang' => $clang,
                    'parent_id' => $parent_id,
                    'name' => $Art->getValue('name'),
                    'status' => $Art->getValue('status'),
                    'priority' => $Art->getValue('priority'),
                    'path' => $Art->getValue('path'),
                    'template_id' => $Art->getValue('template_id'),
                ]));

                $Art->next();
            }
        } else {
            throw new rex_api_exception(rex_i18n::msg('article_doesnt_exist'));
        }

        return $message;
    }

    /**
     * Löscht einen Artikel.
     *
     * @param int $id ArtikelId des Artikels, der gelöscht werden soll
     *
     * @throws rex_api_exception
     *
     * @return string Eine Statusmeldung
     */
    public static function _deleteArticle($id)
    {
        // artikel loeschen

        // kontrolle ob erlaubnis nicht hier.. muss vorher geschehen

        // -> startarticle = 0
        // --> artikelfiles löschen
        // ---> article
        // ---> content
        // ---> clist
        // ---> alist
        // -> startarticle = 1
        // --> rekursiv aufrufen

        if ($id == rex_article::getSiteStartArticleId()) {
            throw new rex_api_exception(rex_i18n::msg('cant_delete_sitestartarticle'));
        }
        if ($id == rex_article::getNotfoundArticleId()) {
            throw new rex_api_exception(rex_i18n::msg('cant_delete_notfoundarticle'));
        }

        $ART = rex_sql::factory();
        $ART->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and clang_id=?', [$id, rex_clang::getStartId()]);

        $message = '';
        if ($ART->getRows() > 0) {
            $parent_id = $ART->getValue('parent_id');
            $message = rex_extension::registerPoint(new rex_extension_point('ART_PRE_DELETED', $message, [
                'id' => $id,
                'parent_id' => $parent_id,
                'name' => $ART->getValue('name'),
                'status' => $ART->getValue('status'),
                'priority' => $ART->getValue('priority'),
                'path' => $ART->getValue('path'),
                'template_id' => $ART->getValue('template_id'),
            ]));

            if (1 == $ART->getValue('startarticle')) {
                $message = rex_i18n::msg('category_deleted');
                $SART = rex_sql::factory();
                $SART->setQuery('select * from ' . rex::getTablePrefix() . 'article where parent_id=? and clang_id=?', [$id, rex_clang::getStartId()]);
                for ($i = 0; $i < $SART->getRows(); ++$i) {
                    self::_deleteArticle($id);
                    $SART->next();
                }
            } else {
                $message = rex_i18n::msg('article_deleted');
            }

            rex_article_cache::delete($id);
            $ART->setQuery('delete from ' . rex::getTablePrefix() . 'article where id=?', [$id]);
            $ART->setQuery('delete from ' . rex::getTablePrefix() . 'article_slice where article_id=?', [$id]);

            // --------------------------------------------------- Listen generieren
            rex_article_cache::deleteLists($parent_id);

            return $message;
        }
        throw new rex_api_exception(rex_i18n::msg('category_doesnt_exist'));
    }

    /**
     * Ändert den Status des Artikels.
     *
     * @param int      $article_id Id des Artikels die gelöscht werden soll
     * @param int      $clang      Id der Sprache
     * @param int|null $status     Status auf den der Artikel gesetzt werden soll, oder NULL wenn zum nächsten Status weitergeschaltet werden soll
     *
     * @throws rex_api_exception
     *
     * @return int Der neue Status des Artikels
     */
    public static function articleStatus($article_id, $clang, $status = null)
    {
        $GA = rex_sql::factory();
        $GA->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and clang_id=?', [$article_id, $clang]);
        if (1 == $GA->getRows()) {
            // Status wurde nicht von außen vorgegeben,
            // => zyklisch auf den nächsten Weiterschalten
            if (null === $status) {
                $newstatus = self::nextStatus($GA->getValue('status'));
            } else {
                $newstatus = $status;
            }

            $EA = rex_sql::factory();
            $EA->setTable(rex::getTablePrefix() . 'article');
            $EA->setWhere(['id' => $article_id, 'clang_id' => $clang]);
            $EA->setValue('status', $newstatus);
            $EA->addGlobalUpdateFields(self::getUser());

            try {
                $EA->update();

                rex_article_cache::delete($article_id, $clang);

                // ----- EXTENSION POINT
                rex_extension::registerPoint(new rex_extension_point('ART_STATUS', null, [
                    'id' => $article_id,
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
     * Gibt alle Stati zurück, die für einen Artikel gültig sind.
     *
     * @return array Array von Stati
     */
    public static function statusTypes()
    {
        static $artStatusTypes;

        if (!$artStatusTypes) {
            $artStatusTypes = [
                // Name, CSS-Class
                [rex_i18n::msg('status_offline'), 'rex-offline', 'rex-icon-offline'],
                [rex_i18n::msg('status_online'), 'rex-online', 'rex-icon-online'],
            ];

            // ----- EXTENSION POINT
            $artStatusTypes = rex_extension::registerPoint(new rex_extension_point('ART_STATUS_TYPES', $artStatusTypes));
        }

        return $artStatusTypes;
    }

    public static function nextStatus($currentStatus)
    {
        $artStatusTypes = self::statusTypes();
        return ($currentStatus + 1) % count($artStatusTypes);
    }

    public static function prevStatus($currentStatus)
    {
        $artStatusTypes = self::statusTypes();
        if (($currentStatus - 1) < 0) {
            return count($artStatusTypes) - 1;
        }

        return ($currentStatus - 1) % count($artStatusTypes);
    }

    /**
     * Berechnet die Prios der Artikel in einer Kategorie neu.
     *
     * @param int $parent_id KategorieId der Kategorie, die erneuert werden soll
     * @param int $clang     ClangId der Kategorie, die erneuert werden soll
     * @param int $new_prio  Neue PrioNr der Kategorie
     * @param int $old_prio  Alte PrioNr der Kategorie
     */
    public static function newArtPrio($parent_id, $clang, $new_prio, $old_prio)
    {
        if ($new_prio != $old_prio) {
            if ($new_prio < $old_prio) {
                $addsql = 'desc';
            } else {
                $addsql = 'asc';
            }

            rex_sql_util::organizePriorities(
                rex::getTable('article'),
                'priority',
                'clang_id=' . (int) $clang . ' AND ((startarticle<>1 AND parent_id=' . (int) $parent_id . ') OR (startarticle=1 AND id=' . (int) $parent_id . '))',
                'priority,updatedate ' . $addsql
            );

            rex_article_cache::deleteLists($parent_id);
            rex_article_cache::deleteMeta($parent_id);

            $ids = rex_sql::factory()->getArray('SELECT id FROM '.rex::getTable('article').' WHERE startarticle=0 AND parent_id = ? GROUP BY id', [$parent_id]);
            foreach ($ids as $id) {
                rex_article_cache::deleteMeta($id['id']);
            }
        }
    }

    /**
     * Konvertiert einen Artikel in eine Kategorie.
     *
     * @param int $art_id Artikel ID des Artikels, der in eine Kategorie umgewandelt werden soll
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function article2category($art_id)
    {
        $sql = rex_sql::factory();
        $parent_id = 0;

        // LANG SCHLEIFE
        foreach (rex_clang::getAllIds() as $clang) {
            // artikel
            $sql->setQuery('select parent_id, name from ' . rex::getTablePrefix() . 'article where id=? and startarticle=0 and clang_id=?', [$art_id, $clang]);

            if (!$parent_id) {
                $parent_id = $sql->getValue('parent_id');
            }

            // artikel updaten
            $sql->setTable(rex::getTablePrefix() . 'article');
            $sql->setWhere(['id' => $art_id, 'clang_id' => $clang]);
            $sql->setValue('startarticle', 1);
            $sql->setValue('catname', $sql->getValue('name'));
            $sql->setValue('catpriority', 100);
            $sql->update();

            rex_category_service::newCatPrio($parent_id, $clang, 0, 100);
        }

        rex_article_cache::deleteLists($parent_id);
        rex_article_cache::delete($art_id);

        foreach (rex_clang::getAllIds() as $clang) {
            rex_extension::registerPoint(new rex_extension_point('ART_TO_CAT', '', [
                'id' => $art_id,
                'clang' => $clang,
            ]));
        }

        return true;
    }

    /**
     * Konvertiert eine Kategorie in einen Artikel.
     *
     * @param int $art_id Artikel ID der Kategorie, die in einen Artikel umgewandelt werden soll
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function category2article($art_id)
    {
        $sql = rex_sql::factory();
        $parent_id = 0;

        // Kategorie muss leer sein
        $sql->setQuery('SELECT pid FROM ' . rex::getTablePrefix() . 'article WHERE parent_id=? LIMIT 1', [$art_id]);
        if (0 != $sql->getRows()) {
            return false;
        }

        // LANG SCHLEIFE
        foreach (rex_clang::getAllIds() as $clang) {
            // artikel
            $sql->setQuery('select parent_id, name from ' . rex::getTablePrefix() . 'article where id=? and startarticle=1 and clang_id=?', [$art_id, $clang]);

            if (!$parent_id) {
                $parent_id = $sql->getValue('parent_id');
            }

            // artikel updaten
            $sql->setTable(rex::getTablePrefix() . 'article');
            $sql->setWhere(['id' => $art_id, 'clang_id' => $clang]);
            $sql->setValue('startarticle', 0);
            $sql->setValue('priority', 100);
            $sql->update();

            self::newArtPrio($parent_id, $clang, 0, 100);
        }

        rex_article_cache::deleteLists($parent_id);
        rex_article_cache::delete($art_id);

        foreach (rex_clang::getAllIds() as $clang) {
            rex_extension::registerPoint(new rex_extension_point('CAT_TO_ART', '', [
                'id' => $art_id,
                'clang' => $clang,
            ]));
        }

        return true;
    }

    /**
     * Konvertiert einen Artikel zum Startartikel der eigenen Kategorie.
     *
     * @param int $neu_id Artikel ID des Artikels, der Startartikel werden soll
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function article2startarticle($neu_id)
    {
        $GAID = [];

        // neuen startartikel holen und schauen ob da
        $neu = rex_sql::factory();
        $neu->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and startarticle=0 and clang_id=?', [$neu_id, rex_clang::getStartId()]);
        if (1 != $neu->getRows()) {
            return false;
        }
        $neu_cat_id = $neu->getValue('parent_id');

        // in oberster kategorie dann return
        if (0 == $neu_cat_id) {
            return false;
        }

        // alten startartikel
        $alt = rex_sql::factory();
        $alt->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and startarticle=1 and clang_id=?', [$neu_cat_id, rex_clang::getStartId()]);
        if (1 != $alt->getRows()) {
            return false;
        }
        $alt_id = $alt->getValue('id');
        $parent_id = $alt->getValue('parent_id');

        // cat felder sammeln. +
        $params = ['path', 'priority', 'catname', 'startarticle', 'catpriority', 'status'];
        $db_fields = rex_structure_element::getClassVars();
        foreach ($db_fields as $field) {
            if ('cat_' == substr($field, 0, 4)) {
                $params[] = $field;
            }
        }

        // LANG SCHLEIFE
        foreach (rex_clang::getAllIds() as $clang) {
            // alter startartikel
            $alt->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and startarticle=1 and clang_id=?', [$neu_cat_id, $clang]);

            // neuer startartikel
            $neu->setQuery('select * from ' . rex::getTablePrefix() . 'article where id=? and startarticle=0 and clang_id=?', [$neu_id, $clang]);

            // alter startartikel updaten
            $alt2 = rex_sql::factory();
            $alt2->setTable(rex::getTablePrefix() . 'article');
            $alt2->setWhere(['id' => $alt_id, 'clang_id' => $clang]);
            $alt2->setValue('parent_id', $neu_id);

            // neuer startartikel updaten
            $neu2 = rex_sql::factory();
            $neu2->setTable(rex::getTablePrefix() . 'article');
            $neu2->setWhere(['id' => $neu_id, 'clang_id' => $clang]);
            $neu2->setValue('parent_id', $alt->getValue('parent_id'));

            // austauschen der definierten paramater
            foreach ($params as $param) {
                $alt2->setValue($param, $neu->getValue($param));
                $neu2->setValue($param, $alt->getValue($param));
            }
            $alt2->update();
            $neu2->update();
        }

        // alle artikel suchen nach |art_id| und pfade ersetzen
        // alles artikel mit parent_id alt_id suchen und ersetzen

        $articles = rex_sql::factory();
        $ia = rex_sql::factory();
        $articles->setQuery('select * from ' . rex::getTablePrefix() . "article where path like '%|$alt_id|%'");
        for ($i = 0; $i < $articles->getRows(); ++$i) {
            $iid = $articles->getValue('id');
            $ipath = str_replace("|$alt_id|", "|$neu_id|", $articles->getValue('path'));

            $ia->setTable(rex::getTablePrefix() . 'article');
            $ia->setWhere(['id' => $iid]);
            $ia->setValue('path', $ipath);
            if ($articles->getValue('parent_id') == $alt_id) {
                $ia->setValue('parent_id', $neu_id);
            }
            $ia->update();
            $GAID[$iid] = $iid;
            $articles->next();
        }

        $GAID[$neu_id] = $neu_id;
        $GAID[$alt_id] = $alt_id;
        $GAID[$parent_id] = $parent_id;

        foreach ($GAID as $gid) {
            rex_article_cache::delete($gid);
        }

        rex_complex_perm::replaceItem('structure', $alt_id, $neu_id);

        foreach (rex_clang::getAllIds() as $clang) {
            rex_extension::registerPoint(new rex_extension_point('ART_TO_STARTARTICLE', '', [
                'id' => $neu_id,
                'id_old' => $alt_id,
                'clang' => $clang,
            ]));
        }

        return true;
    }

    /**
     * Kopiert die Metadaten eines Artikels in einen anderen Artikel.
     *
     * @param int   $from_id    ArtikelId des Artikels, aus dem kopiert werden (Quell ArtikelId)
     * @param int   $to_id      ArtikelId des Artikel, in den kopiert werden sollen (Ziel ArtikelId)
     * @param int   $from_clang ClangId des Artikels, aus dem kopiert werden soll (Quell ClangId)
     * @param int   $to_clang   ClangId des Artikels, in den kopiert werden soll (Ziel ClangId)
     * @param array $params     Array von Spaltennamen, welche kopiert werden sollen
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function copyMeta($from_id, $to_id, $from_clang = 1, $to_clang = 1, $params = [])
    {
        $from_clang = (int) $from_clang;
        $to_clang = (int) $to_clang;
        $from_id = (int) $from_id;
        $to_id = (int) $to_id;
        if (!is_array($params)) {
            $params = [];
        }

        if ($from_id == $to_id && $from_clang == $to_clang) {
            return false;
        }

        $gc = rex_sql::factory();
        $gc->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=? and id=?', [$from_clang, $from_id]);

        if (1 == $gc->getRows()) {
            $uc = rex_sql::factory();
            // $uc->setDebug();
            $uc->setTable(rex::getTablePrefix() . 'article');
            $uc->setWhere(['clang_id' => $to_clang, 'id' => $to_id]);
            $uc->addGlobalUpdateFields(self::getUser());

            foreach ($params as $key => $value) {
                $uc->setValue($value, $gc->getValue($value));
            }

            $uc->update();

            rex_article_cache::deleteMeta($to_id, $to_clang);
            return true;
        }
        return false;
    }

    /**
     * Kopieren eines Artikels von einer Kategorie in eine andere.
     *
     * @param int $id        ArtikelId des zu kopierenden Artikels
     * @param int $to_cat_id KategorieId in die der Artikel kopiert werden soll
     *
     * @return bool|int FALSE bei Fehler, sonst die Artikel Id des neue kopierten Artikels
     */
    public static function copyArticle($id, $to_cat_id)
    {
        $id = (int) $id;
        $to_cat_id = (int) $to_cat_id;
        $new_id = false;

        $user = self::getUser();

        // Artikel in jeder Sprache kopieren
        foreach (rex_clang::getAllIds() as $clang) {
            // validierung der id & from_cat_id
            $from_sql = rex_sql::factory();
            $from_sql->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=? and id=?', [$clang, $id]);

            if (1 == $from_sql->getRows()) {
                // validierung der to_cat_id
                $to_sql = rex_sql::factory();
                $to_sql->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=? and startarticle=1 and id=?', [$clang, $to_cat_id]);

                if (1 == $to_sql->getRows() || 0 == $to_cat_id) {
                    if (1 == $to_sql->getRows()) {
                        $path = $to_sql->getValue('path') . $to_sql->getValue('id') . '|';
                        $catname = $to_sql->getValue('catname');
                    } else {
                        // In RootEbene
                        $path = '|';
                        $catname = $from_sql->getValue('name');
                    }

                    $art_sql = rex_sql::factory();
                    $art_sql->setTable(rex::getTablePrefix() . 'article');
                    if (false === $new_id) {
                        $new_id = $art_sql->setNewId('id');
                    }
                    $art_sql->setValue('id', $new_id); // neuen auto_incrment erzwingen
                    $art_sql->setValue('parent_id', $to_cat_id);
                    $art_sql->setValue('catname', $catname);
                    $art_sql->setValue('catpriority', 0);
                    $art_sql->setValue('path', $path);
                    $art_sql->setValue('name', $from_sql->getValue('name') . ' ' . rex_i18n::msg('structure_copy'));
                    $art_sql->setValue('priority', 99999); // Artikel als letzten Artikel in die neue Kat einfügen
                    $art_sql->setValue('status', 0); // Kopierter Artikel offline setzen
                    $art_sql->setValue('startarticle', 0);
                    $art_sql->addGlobalUpdateFields($user);
                    $art_sql->addGlobalCreateFields($user);

                    // schon gesetzte Felder nicht wieder überschreiben
                    $dont_copy = ['id', 'pid', 'parent_id', 'catname', 'name', 'catpriority', 'path', 'priority', 'status', 'updatedate', 'updateuser', 'createdate', 'createuser', 'startarticle'];

                    foreach (array_diff($from_sql->getFieldnames(), $dont_copy) as $fld_name) {
                        $art_sql->setValue($fld_name, $from_sql->getValue($fld_name));
                    }

                    $art_sql->setValue('clang_id', $clang);
                    $art_sql->insert();

                    $revisions = rex_sql::factory();
                    $revisions->setQuery('select revision from ' . rex::getTablePrefix() . 'article_slice where priority=1 AND article_id=? AND clang_id=? GROUP BY revision', [$id, $clang]);
                    foreach ($revisions as $rev) {
                        // FIXME this dependency is very ugly!
                        // ArticleSlices kopieren
                        rex_content_service::copyContent($id, $new_id, $clang, $clang, $rev->getValue('revision'));
                    }

                    // Prios neu berechnen
                    self::newArtPrio($to_cat_id, $clang, 1, 0);

                    rex_extension::registerPoint(new rex_extension_point('ART_COPIED', null, [
                        'id_source' => $id,
                        'id' => $new_id,
                        'clang' => $clang,
                        'category_id' => $to_cat_id,
                    ]));
                } else {
                    return false;
                }
            } else {
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
     * Verschieben eines Artikels von einer Kategorie in eine Andere.
     *
     * @param int $id          ArtikelId des zu verschiebenden Artikels
     * @param int $from_cat_id KategorieId des Artikels, der Verschoben wird
     * @param int $to_cat_id   KategorieId in die der Artikel verschoben werden soll
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function moveArticle($id, $from_cat_id, $to_cat_id)
    {
        $id = (int) $id;
        $to_cat_id = (int) $to_cat_id;
        $from_cat_id = (int) $from_cat_id;

        if ($from_cat_id == $to_cat_id) {
            return false;
        }

        // Artikel in jeder Sprache verschieben
        foreach (rex_clang::getAllIds() as $clang) {
            // validierung der id & from_cat_id
            $from_sql = rex_sql::factory();
            $from_sql->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=? and startarticle<>1 and id=? and parent_id=?', [$clang, $id, $from_cat_id]);

            if (1 == $from_sql->getRows()) {
                // validierung der to_cat_id
                $to_sql = rex_sql::factory();
                $to_sql->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=? and startarticle=1 and id=?', [$clang, $to_cat_id]);

                if (1 == $to_sql->getRows() || 0 == $to_cat_id) {
                    if (1 == $to_sql->getRows()) {
                        $parent_id = $to_sql->getValue('id');
                        $path = $to_sql->getValue('path') . $to_sql->getValue('id') . '|';
                        $catname = $to_sql->getValue('catname');
                    } else {
                        // In RootEbene
                        $parent_id = 0;
                        $path = '|';
                        $catname = $from_sql->getValue('name');
                    }

                    $art_sql = rex_sql::factory();
                    //$art_sql->setDebug();

                    $art_sql->setTable(rex::getTablePrefix() . 'article');
                    $art_sql->setValue('parent_id', $parent_id);
                    $art_sql->setValue('path', $path);
                    $art_sql->setValue('catname', $catname);
                    // Artikel als letzten Artikel in die neue Kat einfügen
                    $art_sql->setValue('priority', '99999');
                    // Kopierter Artikel offline setzen
                    $art_sql->setValue('status', $from_sql->getValue('status'));
                    $art_sql->addGlobalUpdateFields(self::getUser());

                    $art_sql->setWhere('clang_id="' . $clang . '" and startarticle<>1 and id="' . $id . '" and parent_id="' . $from_cat_id . '"');
                    $art_sql->update();

                    // Prios neu berechnen
                    self::newArtPrio($to_cat_id, $clang, 1, 0);
                    self::newArtPrio($from_cat_id, $clang, 1, 0);

                    rex_extension::registerPoint(new rex_extension_point('ART_MOVED', null, [
                        'id' => $id,
                        'clang' => $clang,
                        'category_id' => $parent_id,
                    ]));
                } else {
                    return false;
                }
            } else {
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
     * Checks whether the required array key $keyName isset.
     *
     * @param array  $array   The array
     * @param string $keyName The key
     *
     * @throws rex_api_exception
     */
    protected static function reqKey($array, $keyName)
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
