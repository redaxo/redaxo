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
}