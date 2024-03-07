<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Path;

class rex_template_cache
{
    public static function delete(int $id): void
    {
        rex_file::delete(self::getPath($id));
        self::deleteKeyMapping();
    }

    public static function deleteKeyMapping(): void
    {
        rex_file::delete(self::getKeyMappingPath());
    }

    public static function generate(int $id): void
    {
        $sql = Sql::factory();
        $sql->setQuery('SELECT * FROM ' . Core::getTable('template') . ' WHERE id = ?', [$id]);

        if (1 !== $sql->getRows()) {
            throw new rex_exception('Template with id "' . $id . '" does not exist.');
        }

        $content = $sql->getValue('content');
        $content = rex_var::parse($content, rex_var::ENV_FRONTEND, 'template');

        $path = self::getPath($id);
        if (!rex_file::put($path, $content)) {
            throw new rex_exception('Unable to generate template "' . $id . '".');
        }

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path);
        }
    }

    public static function generateKeyMapping(): void
    {
        $data = Sql::factory()->getArray('SELECT id, `key` FROM ' . Core::getTable('template') . ' WHERE `key` IS NOT NULL');
        $mapping = array_column($data, 'key', 'id');

        if (!rex_file::putCache(self::getKeyMappingPath(), $mapping)) {
            throw new rex_exception('Unable to generate template key mapping.');
        }
    }

    public static function getPath(int $id): string
    {
        return Path::coreCache('structure/templates/' . $id . '.template');
    }

    public static function getKeyMappingPath(): string
    {
        return Path::coreCache('structure/templates/template_key_mapping.cache');
    }
}
