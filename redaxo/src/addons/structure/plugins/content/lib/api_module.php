<?php

/**
 * @package redaxo\structure\content
 */
class rex_module
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string|null
     */
    private $key;

    public function __construct(int $module_id)
    {
        $this->id = $module_id;
        $this->key = '';
    }

    public static function forKey(string $module_key): ?self
    {
        $mapping = self::getKeyMapping();

        if (false !== $id = array_search($module_key, $mapping, true)) {
            $module = new self($id);
            $module->key == $module_key;

            return $module;
        }

        return null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        // key will never be empty string in the db
        if ('' === $this->key) {
            $this->key = self::getKeyMapping()[$this->id] ?? null;
            assert('' !== $this->key);
        }

        return $this->key;
    }

    public static function deleteKeyMappingCache(): void
    {
        rex_file::delete(self::getKeyMappingPath());
    }

    /**
     * @return array<int, string>
     */
    private static function getKeyMapping(): array
    {
        static $mapping;

        if (null !== $mapping) {
            return $mapping;
        }

        $file = self::getKeyMappingPath();
        $mapping = rex_file::getCache($file, null);

        if (null !== $mapping) {
            return $mapping;
        }

        $data = rex_sql::factory()->getArray('SELECT id, `key` FROM '.rex::getTable('module').' WHERE `key` IS NOT NULL');
        $mapping = array_column($data, 'key', 'id');
        rex_file::putCache($file, $mapping);

        return $mapping;
    }

    private static function getKeyMappingPath(): string
    {
        return rex_path::addonCache('structure', 'module_key_mapping.cache');
    }
}
