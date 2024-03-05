<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;

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
        $data = Sql::factory()->getArray('SELECT id, `key` FROM ' . Core::getTable('module') . ' WHERE `key` IS NOT NULL');
        $mapping = array_column($data, 'key', 'id');

        if (!rex_file::putCache(self::getKeyMappingPath(), $mapping)) {
            throw new rex_exception('Unable to generate module key mapping.');
        }
    }

    public static function getKeyMappingPath(): string
    {
        return rex_path::coreCache('structure/module_key_mapping.cache');
    }
}
