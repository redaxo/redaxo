<?php

/**
 * @package redaxo\structure
 */
class rex_article_cache
{
    /**
     * Löscht die gecachten Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel-Cache in allen Sprachen gelöscht.
     *
     * @param int $id      ArtikelId des Artikels
     * @param int $clangId ClangId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function delete($id, $clangId = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        foreach (rex_clang::getAllIds() as $otherClangId) {
            if (null !== $clangId && $clangId != $otherClangId) {
                continue;
            }

            self::deleteMeta($id, $clangId);
            self::deleteContent($id, $clangId);
        }
        self::deleteLists($id);

        return true;
    }

    /**
     * Löscht die gecachten Meta-Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel in allen Sprachen gelöscht.
     *
     * @param int $id      ArtikelId des Artikels
     * @param int $clangId ClangId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function deleteMeta($id, $clangId = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $otherClangId) {
            if (null !== $clangId && $clangId != $otherClangId) {
                continue;
            }

            rex_file::delete($cachePath . $id . '.' . $otherClangId . '.article');
            rex_article::clearInstance([$id, $otherClangId]);
            rex_category::clearInstance([$id, $otherClangId]);
        }

        return true;
    }

    /**
     * Löscht die gecachten Content-Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel in allen Sprachen gelöscht.
     *
     * @param int $id      ArtikelId des Artikels
     * @param int $clangId ClangId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function deleteContent($id, $clangId = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $otherClangId) {
            if (null !== $clangId && $clangId != $otherClangId) {
                continue;
            }

            rex_file::delete($cachePath . $id . '.' . $otherClangId . '.content');
        }

        return true;
    }

    /**
     * Löscht die gecachten List-Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel in allen Sprachen gelöscht.
     *
     * @param int $id ArtikelId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function deleteLists($id)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (['alist', 'clist'] as $list) {
            rex_file::delete($cachePath . $id . '.' . $list);
            rex_structure_element::clearInstanceList([$id, $list]);
        }

        return true;
    }

    /**
     * Generiert den Artikel-Cache der Metainformationen.
     *
     * @param int $articleId Id des zu generierenden Artikels
     * @param int $clangId    ClangId des Artikels
     *
     * @return bool|string TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
     */
    public static function generateMeta($articleId, $clangId = null)
    {
        // sanity check
        if ($articleId <= 0) {
            return false;
        }

        $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=' . (int) $articleId;
        if (null !== $clangId) {
            $qry .= ' AND clang_id=' . (int) $clangId;
        }

        $sql = rex_sql::factory();
        $sql->setQuery($qry);
        $fieldnames = $sql->getFieldnames();
        foreach ($sql as $row) {
            $clang = $row->getValue('clang_id');

            // --------------------------------------------------- Artikelparameter speichern
            $params = ['last_update_stamp' => time()];
            foreach ($fieldnames as $field) {
                switch ($field) {
                    case 'createdate':
                    case 'updatedate':
                        $params[$field] = $row->getDateTimeValue($field);
                        break;
                    default:
                        $params[$field] = $row->getValue($field);
                }
            }

            $articleFile = rex_path::addonCache('structure', "$articleId.$clang.article");
            if (false === rex_file::putCache($articleFile, $params)) {
                return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
            }
        }

        return true;
    }

    /**
     * Generiert alle *.alist u. *.clist Dateien einer Kategorie/eines Artikels.
     *
     * @param int $parentId KategorieId oder ArtikelId, die erneuert werden soll
     *
     * @return bool|string TRUE wenn der Artikel gelöscht wurde, sonst eine Fehlermeldung
     */
    public static function generateLists($parentId)
    {
        // sanity check
        if ($parentId < 0) {
            return false;
        }

        // --------------------------------------- ARTICLE LIST

        $GC = rex_sql::factory();
        // $GC->setDebug();
        $GC->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=:clang AND ((parent_id=:id and startarticle=0) OR (id=:id and startarticle=1)) order by priority,name', ['id' => $parentId, 'clang' => rex_clang::getStartId()]);

        $cacheArray = [];
        foreach ($GC as $row) {
            $cacheArray[] = (int) $row->getValue('id');
        }

        $articleListFile = rex_path::addonCache('structure', $parentId . '.alist');
        if (false === rex_file::putCache($articleListFile, $cacheArray)) {
            return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
        }

        // --------------------------------------- CAT LIST

        $GC = rex_sql::factory();
        $GC->setQuery('select * from ' . rex::getTablePrefix() . 'article where parent_id=:id and clang_id=:clang and startarticle=1 order by catpriority,name', ['id' => $parentId, 'clang' => rex_clang::getStartId()]);

        $cacheArray = [];
        foreach ($GC as $row) {
            $cacheArray[] = (int) $row->getValue('id');
        }

        $articleCategoriesFile = rex_path::addonCache('structure', $parentId . '.clist');
        if (false === rex_file::putCache($articleCategoriesFile, $cacheArray)) {
            return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
        }

        return true;
    }
}
