<?php

namespace Redaxo\Core\Content;

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Exception\RuntimeException;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;

class ModuleCache
{
    public static function delete(int $id): void
    {
        self::deleteKeyMapping();
    }

    public static function deleteKeyMapping(): void
    {
        File::delete(self::getKeyMappingPath());
    }

    public static function generateKeyMapping(): void
    {
        $data = Sql::factory()->getArray('SELECT id, `key` FROM ' . Core::getTable('module') . ' WHERE `key` IS NOT NULL');
        $mapping = array_column($data, 'key', 'id');

        if (!File::putCache(self::getKeyMappingPath(), $mapping)) {
            throw new RuntimeException('Unable to generate module key mapping.');
        }
    }

    public static function getKeyMappingPath(): string
    {
        return Path::coreCache('structure/module_key_mapping.cache');
    }
}
