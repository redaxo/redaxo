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
     * @param int $id    ArtikelId des Artikels
     * @param int $clang ClangId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function delete($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        foreach (rex_clang::getAllIds() as $_clang) {
            if (null !== $clang && $clang != $_clang) {
                continue;
            }

            self::deleteMeta($id, $clang);
            self::deleteContent($id, $clang);
        }
        self::deleteLists($id);

        return true;
    }

    /**
     * Löscht die gecachten Meta-Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel in allen Sprachen gelöscht.
     *
     * @param int $id    ArtikelId des Artikels
     * @param int $clang ClangId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function deleteMeta($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $_clang) {
            if (null !== $clang && $clang != $_clang) {
                continue;
            }

            rex_file::delete($cachePath . $id . '.' . $_clang . '.article');
            rex_article::clearInstance([$id, $_clang]);
            rex_category::clearInstance([$id, $_clang]);
        }

        return true;
    }

    /**
     * Löscht die gecachten Content-Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel in allen Sprachen gelöscht.
     *
     * @param int $id    ArtikelId des Artikels
     * @param int $clang ClangId des Artikels
     *
     * @return bool True on success, False on errro
     */
    public static function deleteContent($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $_clang) {
            if (null !== $clang && $clang != $_clang) {
                continue;
            }

            rex_file::delete($cachePath . $id . '.' . $_clang . '.content');
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
     * @param int $article_id Id des zu generierenden Artikels
     * @param int $clang      ClangId des Artikels
     *
     * @return bool|string TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
     */
    public static function generateMeta($article_id, $clang = null)
    {
        // sanity check
        if ($article_id <= 0) {
            return false;
        }

        $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=' . (int) $article_id;
        if (null !== $clang) {
            $qry .= ' AND clang_id=' . (int) $clang;
        }

        $sql = rex_sql::factory();
        $sql->setQuery($qry);
        $fieldnames = $sql->getFieldnames();
        foreach ($sql as $row) {
            $_clang = $row->getValue('clang_id');

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

            $article_file = rex_path::addonCache('structure', "$article_id.$_clang.article");
            if (false === rex_file::putCache($article_file, $params)) {
                return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
            }
        }

        return true;
    }

    /**
     * Generiert alle *.alist u. *.clist Dateien einer Kategorie/eines Artikels.
     *
     * @param int $parent_id KategorieId oder ArtikelId, die erneuert werden soll
     *
     * @return bool|string TRUE wenn der Artikel gelöscht wurde, sonst eine Fehlermeldung
     */
    public static function generateLists($parent_id)
    {
        // sanity check
        if ($parent_id < 0) {
            return false;
        }

        // --------------------------------------- ARTICLE LIST

        $GC = rex_sql::factory();
        // $GC->setDebug();
        $GC->setQuery('select * from ' . rex::getTablePrefix() . 'article where clang_id=:clang AND ((parent_id=:id and startarticle=0) OR (id=:id and startarticle=1)) order by priority,name', ['id' => $parent_id, 'clang' => rex_clang::getStartId()]);

        $cacheArray = [];
        foreach ($GC as $row) {
            $cacheArray[] = (int) $row->getValue('id');
        }

        $article_list_file = rex_path::addonCache('structure', $parent_id . '.alist');
        if (false === rex_file::putCache($article_list_file, $cacheArray)) {
            return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
        }

        // --------------------------------------- CAT LIST

        $GC = rex_sql::factory();
        $GC->setQuery('select * from ' . rex::getTablePrefix() . 'article where parent_id=:id and clang_id=:clang and startarticle=1 order by catpriority,name', ['id' => $parent_id, 'clang' => rex_clang::getStartId()]);

        $cacheArray = [];
        foreach ($GC as $row) {
            $cacheArray[] = (int) $row->getValue('id');
        }

        $article_categories_file = rex_path::addonCache('structure', $parent_id . '.clist');
        if (false === rex_file::putCache($article_categories_file, $cacheArray)) {
            return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
        }

        return true;
    }
}
