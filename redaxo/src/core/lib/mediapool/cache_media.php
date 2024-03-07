<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\File;

class rex_media_cache
{
    /**
     * Löscht die gecachte Medium-Datei.
     *
     * @param string $filename Dateiname
     * @return void
     */
    public static function delete($filename)
    {
        File::delete(rex_path::coreCache('mediapool/' . $filename . '.media'));
        rex_media::clearInstance($filename);
        self::deleteLists();
    }

    /**
     * Löscht die gecachten Dateien der Media-Kategorie.
     *
     * @param int $categoryId Id der Media-Kategorie
     * @return void
     */
    public static function deleteCategory($categoryId)
    {
        File::delete(rex_path::coreCache('mediapool/' . $categoryId . '.mcat'));
        rex_media_category::clearInstance($categoryId);
        self::deleteCategoryLists();
    }

    /**
     * Löscht die gecachten Media-Listen.
     * @return void
     */
    public static function deleteLists()
    {
        $cachePath = rex_path::coreCache('mediapool/');

        $glob = glob($cachePath . '*.mlist', GLOB_NOSORT);
        if (is_array($glob)) {
            foreach ($glob as $file) {
                File::delete($file);
            }
        }
        rex_media_category::clearInstanceListPool();
    }

    /**
     * Löscht die gecachte Liste mit den Media der Kategorie.
     *
     * @param int $categoryId Id der Media-Kategorie
     * @return void
     */
    public static function deleteList($categoryId)
    {
        File::delete(rex_path::coreCache('mediapool/' . $categoryId . '.mlist'));
        rex_media_category::clearInstanceList([$categoryId, 'media']);
    }

    /**
     * Löscht die gecachten Media-Kategorien-Listen.
     * @return void
     */
    public static function deleteCategoryLists()
    {
        $cachePath = rex_path::coreCache('mediapool/');

        $glob = glob($cachePath . '*.mclist', GLOB_NOSORT);
        if (is_array($glob)) {
            foreach ($glob as $file) {
                File::delete($file);
            }
        }
        rex_media_category::clearInstanceListPool();
    }

    /**
     * Löscht die gecachte Media-Kategorien-Liste.
     *
     * @param int $categoryId Id der Media-Kategorie
     * @return void
     */
    public static function deleteCategoryList($categoryId)
    {
        File::delete(rex_path::coreCache('mediapool/' . $categoryId . '.mclist'));
        rex_media_category::clearInstanceList([$categoryId, 'children']);
    }

    /**
     * Generiert den Cache des Mediums.
     *
     * @param string $filename Dateiname des zu generierenden Mediums
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generate($filename)
    {
        $query = 'SELECT * FROM ' . Core::getTable('media') . ' WHERE filename = ?';
        $sql = Sql::factory();
        // $sql->setDebug();
        $sql->setQuery($query, [$filename]);

        if (0 == $sql->getRows()) {
            return false;
        }

        $cacheArray = [];
        foreach ($sql->getFieldNames() as $fieldName) {
            $cacheArray[$fieldName] = match ($fieldName) {
                'createdate', 'updatedate' => $sql->getDateTimeValue($fieldName),
                default => $sql->getValue($fieldName),
            };
        }

        $mediaFile = rex_path::coreCache('mediapool/' . $filename . '.media');
        return File::putCache($mediaFile, $cacheArray);
    }

    /**
     * Generiert den Cache der Media-Kategorie.
     *
     * @param int $categoryId Id des zu generierenden Media-Kategorie
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generateCategory($categoryId)
    {
        // sanity check
        if ($categoryId < 0) {
            return false;
        }

        $query = 'SELECT * FROM ' . Core::getTable('media_category') . ' WHERE id = ?';
        $sql = Sql::factory();
        // $sql->setDebug();
        $sql->setQuery($query, [$categoryId]);

        if (0 == $sql->getRows()) {
            return false;
        }

        $cacheArray = [];
        foreach ($sql->getFieldNames() as $fieldName) {
            $cacheArray[$fieldName] = match ($fieldName) {
                'createdate', 'updatedate' => $sql->getDateTimeValue($fieldName),
                default => $sql->getValue($fieldName),
            };
        }

        $catFile = rex_path::coreCache('mediapool/' . $categoryId . '.mcat');
        return File::putCache($catFile, $cacheArray);
    }

    /**
     * Generiert eine Liste mit den Media einer Kategorie.
     *
     * @param int $categoryId Id der Kategorie
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generateList($categoryId)
    {
        // sanity check
        if ($categoryId < 0) {
            return false;
        }

        $query = 'SELECT filename FROM ' . Core::getTable('media') . ' WHERE category_id = ?';
        $sql = Sql::factory();
        $sql->setQuery($query, [$categoryId]);

        $cacheArray = [];
        for ($i = 0; $i < $sql->getRows(); ++$i) {
            $cacheArray[] = $sql->getValue('filename');
            $sql->next();
        }

        $listFile = rex_path::coreCache('mediapool/' . $categoryId . '.mlist');
        return File::putCache($listFile, $cacheArray);
    }

    /**
     * Generiert eine Liste mit den Kindkategorien einer Kategorie.
     *
     * @param int $categoryId Id der Kategorie
     *
     * @return bool TRUE bei Erfolg, sonst FALSE
     */
    public static function generateCategoryList($categoryId)
    {
        // sanity check
        if ($categoryId < 0) {
            return false;
        }

        $query = 'SELECT id, cast( name AS SIGNED ) AS sort FROM ' . Core::getTable('media_category') . ' WHERE parent_id = ? ORDER BY sort, name';
        $sql = Sql::factory();
        // $sql->setDebug();
        $sql->setQuery($query, [$categoryId]);

        $cacheArray = [];
        for ($i = 0; $i < $sql->getRows(); ++$i) {
            $cacheArray[] = $sql->getValue('id');
            $sql->next();
        }

        $listFile = rex_path::coreCache('mediapool/' . $categoryId . '.mclist');
        return File::putCache($listFile, $cacheArray);
    }
}
