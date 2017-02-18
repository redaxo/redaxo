<?php
class rex_media_category_service {
    /**
     * @param $name string
     * @param $parent rex_media_category|null
     * @return string Eine Erfolgsmeldung
     */
    public static function addCategory($name, $parent) {
        $db = rex_sql::factory();

        // root category
        $parent_id = 0;
        $path = '|';
        if ($parent) {
            $parent_id = $parent->getId();
            $path = $parent->getPath() . $parent->getId().'|';
        }

        $db->setTable(rex::getTablePrefix() . 'media_category');
        $db->setValue('name', $name);
        $db->setValue('parent_id', $parent_id);
        $db->setValue('path', $path);
        $db->addGlobalCreateFields();
        $db->addGlobalUpdateFields();

        $db->insert();

        rex_media_cache::deleteCategoryList($parent_id);

        return rex_i18n::msg('pool_kat_saved', $name);
    }

    /**
     * @param $categoryId int
     * @return string Eine Erfolgsmeldung
     * @throws rex_api_exception
     */
    public static function deleteCategory($categoryId) {
        $gf = rex_sql::factory();
        $gf->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media WHERE category_id=?', [$categoryId]);
        $gd = rex_sql::factory();
        $gd->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'media_category WHERE parent_id=?', [$categoryId]);
        if ($gf->getRows() == 0 && $gd->getRows() == 0) {
            $gf->setQuery('DELETE FROM ' . rex::getTablePrefix() . 'media_category WHERE id=?', [$categoryId]);
            rex_media_cache::deleteCategory($categoryId);
            rex_media_cache::deleteLists();
        } else {
            throw new rex_api_exception(rex_i18n::msg('pool_kat_not_deleted'));
        }

        return rex_i18n::msg('pool_kat_deleted');
    }
}