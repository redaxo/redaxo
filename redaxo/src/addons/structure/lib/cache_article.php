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
     * @return boolean True on success, False on errro
     */
    public static function delete($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        foreach (rex_clang::getAllIds() as $_clang) {
            if ($clang !== null && $clang != $_clang) {
            continue;
            }

            self::deleteMeta($id, $clang);
            self::deleteContent($id, $clang);
            self::deleteLists($id, $clang);
        }

        return true;
    }

    /**
     * Löscht die gecachten Meta-Dateien eines Artikels. Wenn keine clang angegeben, wird
     * der Artikel in allen Sprachen gelöscht.
     *
     * @param int $id    ArtikelId des Artikels
     * @param int $clang ClangId des Artikels
     *
     * @return boolean True on success, False on errro
     */
    public static function deleteMeta($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $_clang) {
            if ($clang !== null && $clang != $_clang) {
            continue;
            }

            rex_file::delete($cachePath . $id . '.' . $_clang . '.article');
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
     * @return boolean True on success, False on errro
     */
    public static function deleteContent($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $_clang) {
            if ($clang !== null && $clang != $_clang) {
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
     * @param int $id    ArtikelId des Artikels
     * @param int $clang ClangId des Artikels
     *
     * @return boolean True on success, False on errro
     */
    public static function deleteLists($id, $clang = null)
    {
        // sanity check
        if ($id < 0) {
            return false;
        }

        $cachePath = rex_path::addonCache('structure');

        foreach (rex_clang::getAllIds() as $_clang) {
            if ($clang !== null && $clang != $_clang) {
            continue;
            }

            rex_file::delete($cachePath . $id . '.' . $_clang . '.alist');
            rex_file::delete($cachePath . $id . '.' . $_clang . '.clist');
        }

        return true;
    }


    /**
     * Generiert den Artikel-Cache der Metainformationen.
     *
     * @param int $article_id Id des zu generierenden Artikels
     * @param int $clang      ClangId des Artikels
     * @return bool TRUE bei Erfolg, FALSE wenn eine ungütlige article_id übergeben wird, sonst eine Fehlermeldung
     */
    public static function generateMeta($article_id, $clang = null)
    {
        // sanity check
        if ($article_id <= 0) {
            return false;
        }

        $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'article WHERE id=' . (int) $article_id;
        if ($clang !== null) {
            $qry .= ' AND clang=' . (int) $clang;
        }

        $sql = rex_sql::factory();
        $sql->setQuery($qry);
        foreach ($sql as $row) {
            $_clang = $row->getValue('clang');

            // --------------------------------------------------- Artikelparameter speichern
            $params = [
                'article_id' => $article_id,
                'last_update_stamp' => time()
            ];

            $class_vars = rex_structure_element::getClassVars();
            unset($class_vars[array_search('id', $class_vars)]);
            $db_fields = $class_vars;

            foreach ($db_fields as $field) {
                $params[$field] = $row->getValue($field);
            }

            $cacheArray = [];
            foreach ($params as $name => $value) {
                $cacheArray[$name][$_clang] = $value;
            }

            $article_file = rex_path::addonCache('structure', "$article_id.$_clang.article");
            if (rex_file::putCache($article_file, $cacheArray) === false) {
                return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
            }
        }

        return true;
    }

    /**
     * Generiert alle *.alist u. *.clist Dateien einer Kategorie/eines Artikels
     *
     * @param integer $parent_id KategorieId oder ArtikelId, die erneuert werden soll
     * @param int     $clang
     * @return bool TRUE wenn der Artikel gelöscht wurde, sonst eine Fehlermeldung
     */
    public static function generateLists($parent_id, $clang = null)
    {
        // sanity check
        if ($parent_id < 0) {
            return false;
        }


        // generiere listen
        //
        //
        // -> je nach clang
        // --> artikel listen
        // --> catgorie listen
        //

        foreach (rex_clang::getAllIds() as $_clang) {
            if ($clang !== null && $clang != $_clang) {
            continue;
            }

            // --------------------------------------- ARTICLE LIST

            $GC = rex_sql::factory();
            // $GC->setDebug();
            $GC->setQuery('select * from ' . rex::getTablePrefix() . "article where (parent_id=$parent_id and clang=$_clang and startarticle=0) OR (id=$parent_id and clang=$_clang and startarticle=1) order by priority,name");

            $cacheArray = [];
            for ($i = 0; $i < $GC->getRows(); $i ++) {
                $cacheArray[$i] = $GC->getValue('id');
                $GC->next();
            }

            $article_list_file = rex_path::addonCache('structure', "$parent_id.$_clang.alist");
            if (rex_file::putCache($article_list_file, $cacheArray) === false) {
                return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
            }

            // --------------------------------------- CAT LIST

            $GC = rex_sql::factory();
            $GC->setQuery('select * from ' . rex::getTablePrefix() . "article where parent_id=$parent_id and clang=$_clang and startarticle=1 order by catpriority,name");

            $cacheArray = [];
            for ($i = 0; $i < $GC->getRows(); $i ++) {
                $cacheArray[$i] = $GC->getValue('id');
                $GC->next();
            }

            $article_categories_file = rex_path::addonCache('structure', "$parent_id.$_clang.clist");
            if (rex_file::putCache($article_categories_file, $cacheArray) === false) {
                return rex_i18n::msg('article_could_not_be_generated') . ' ' . rex_i18n::msg('check_rights_in_directory') . rex_path::addonCache('structure');
            }
        }

        return true;
    }
}
