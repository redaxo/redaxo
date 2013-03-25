<?php

/**
 * @package redaxo\mediapool
 */
class rex_media_cache
{
    /**
     * Löscht die gecachte Medium-Datei.
     *
     * @param string $filename Dateiname
     */
    public static function delete($filename)
    {
        rex_file::delete(rex_path::addonCache('mediapool', $filename . '.media'));
        rex_media::removeInstance($filename);
        self::deleteLists();
    }

    /**
     * Löscht die gecachten Dateien der Media-Kategorie.
     *
     * @param int $category_id Id der Media-Kategorie
     */
    public static function deleteCategory($category_id)
    {
        rex_file::delete(rex_path::addonCache('mediapool', $category_id . '.mcat'));
        rex_media_category::removeInstance($category_id);
        self::deleteCategoryLists();
    }

    /**
     * Löscht die gecachten Media-Listen.
     *
     * @return void
     */
    public static function deleteLists()
    {
        $cachePath = rex_path::addonCache('mediapool');

        $glob = glob($cachePath . '*.mlist');
        if (is_array($glob)) {
            foreach ($glob as $file) {
                rex_file::delete($file);
            }
        }
    }

    /**
     * Löscht die gecachte Liste mit den Media der Kategorie.
     *
     * @param int $category_id Id der Media-Kategorie
     */
    public static function deleteList($category_id)
    {
        rex_file::delete(rex_path::addonCache('mediapool', $category_id . '.mlist'));
    }

    /**
     * Löscht die gecachten Media-Kategorien-Listen.
     *
     * @return void
     */
    public static function deleteCategoryLists()
    {
        $cachePath = rex_path::addonCache('mediapool');

        $glob = glob($cachePath . '*.mclist');
        if (is_array($glob)) {
            foreach ($glob as $file) {
                rex_file::delete($file);
            }
        }
    }

    /**
     * Löscht die gecachte Media-Kategorien-Liste.
     *
     * @param int $category_id Id der Media-Kategorie
     */
    public static function deleteCategoryList($category_id)
    {
        rex_file::delete(rex_path::addonCache('mediapool', $category_id . '.mclist'));
    }

    /**
     * Generiert den Cache des Mediums.
     *
     * @param string $filename Dateiname des zu generierenden Mediums
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generate($filename)
    {
        $query = 'SELECT * FROM ' . rex_media :: _getTableName() . ' WHERE filename = "' . $filename . '"';
        $sql = rex_sql::factory();
        //$sql->setDebug();
        $sql->setQuery($query);

        if ($sql->getRows() == 0) {
            return false;
        }

        $cacheArray = [];
        foreach ($sql->getFieldNames() as $fieldName) {
            switch ($fieldName) {
                case 'createdate':
                case 'updatedate':
                    $cacheArray[$fieldName] = $sql->getDateTimeValue($fieldName);
                    break;
                default:
                    $cacheArray[$fieldName] = $sql->getValue($fieldName);
            }
        }

        $media_file = rex_path::addonCache('mediapool', $filename . '.media');
        if (rex_file::putCache($media_file, $cacheArray)) {
            return true;
        }

        return false;
    }

    /**
     * Generiert den Cache der Media-Kategorie.
     *
     * @param int $category_id Id des zu generierenden Media-Kategorie
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generateCategory($category_id)
    {
        // sanity check
        if ($category_id < 0) {
            return false;
        }

        $query = 'SELECT * FROM ' . rex_media_category :: _getTableName() . ' WHERE id = ' . $category_id;
        $sql = rex_sql::factory();
        //$sql->setDebug();
        $sql->setQuery($query);

        if ($sql->getRows() == 0) {
            return false;
        }

        $cacheArray = [];
        foreach ($sql->getFieldNames() as $fieldName) {
            switch ($fieldName) {
                case 'createdate':
                case 'updatedate':
                    $cacheArray[$fieldName] = $sql->getDateTimeValue($fieldName);
                    break;
                default:
                    $cacheArray[$fieldName] = $sql->getValue($fieldName);
            }
        }

        $cat_file = rex_path::addonCache('mediapool', $category_id . '.mcat');
        if (rex_file::putCache($cat_file, $cacheArray)) {
            return true;
        }

        return false;
    }

    /**
     * Generiert eine Liste mit den Media einer Kategorie.
     *
     * @param int $category_id Id der Kategorie
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generateList($category_id)
    {
        // sanity check
        if ($category_id < 0) {
            return false;
        }

        $query = 'SELECT filename FROM ' . rex_media :: _getTableName() . ' WHERE category_id = ' . $category_id;
        $sql = rex_sql::factory();
        $sql->setQuery($query);

        $cacheArray = [];
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $cacheArray[] = $sql->getValue('filename');
            $sql->next();
        }

        $list_file = rex_path::addonCache('mediapool', $category_id . '.mlist');
        if (rex_file::putCache($list_file, $cacheArray)) {
            return true;
        }

        return false;
    }

    /**
     * Generiert eine Liste mit den Kindkategorien einer Kategorie.
     *
     * @param int $category_id Id der Kategorie
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generateCategoryList($category_id)
    {
        // sanity check
        if ($category_id < 0) {
            return false;
        }

        $query = 'SELECT id, cast( name AS SIGNED ) AS sort FROM ' . rex_media_category :: _getTableName() . ' WHERE parent_id = ' . $category_id . ' ORDER BY sort, name';
        $sql = rex_sql::factory();
        //$sql->setDebug();
        $sql->setQuery($query);

        $cacheArray = [];
        for ($i = 0; $i < $sql->getRows(); $i++) {
            $cacheArray[] = $sql->getValue('id');
            $sql->next();
        }

        $list_file = rex_path::addonCache('mediapool', $category_id . '.mclist');
        if (rex_file::putCache($list_file, $cacheArray)) {
            return true;
        }

        return false;
    }
}
