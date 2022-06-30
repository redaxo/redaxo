<?php

/**
 * @package redaxo\structure\content
 */
class rex_module_cache
{
    public static function delete(int $id): void
    {
        self::deleteKeyMapping();
    }

    public static function deleteKeyMapping(): void
    {
        rex_file::delete(self::getKeyMappingPath());
    }

    public static function generateKeyMapping(): void
    {
        $data = rex_sql::factory()->getArray('SELECT id, `key` FROM '.rex::getTable('module').' WHERE `key` IS NOT NULL');
        $mapping = array_column($data, 'key', 'id');

        if (!rex_file::putCache(self::getKeyMappingPath(), $mapping)) {
            throw new rex_exception('Unable to generate module key mapping.');
        }
    }

    public static function getKeyMappingPath(): string
    {
        return rex_path::addonCache('structure', 'module_key_mapping.cache');
    }
}
