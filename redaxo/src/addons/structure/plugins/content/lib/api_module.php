<?php

/**
 * @package redaxo\structure\content
 */
class rex_module
{
    /**
     * @var int
     */
    private $module_id;
    /**
     * @var string|null
     */
    private $key;

    public function __construct(int $module_id)
    {
        $this->module_id = $module_id;
        $this->key = '';
    }

    public static function forKey(string $module_key): ?self
    {
        $sql = rex_sql::factory();
        $sql->setQuery('select id from '. rex::getTable('module') .' where `key`=?', [$module_key]);

        if (1 == $sql->getRows()) {
            $module_id = $sql->getValue('id');

            $module = new self($module_id);
            $module->key = $module_key;
            return $module;
        }
        return null;
    }

    public function getId(): int
    {
        return $this->module_id;
    }

    public function getKey(): ?string
    {
        // key will never be empty string in the db
        if ('' === $this->key) {
            $sql = rex_sql::factory();
            $sql->setQuery('select `key` from '. rex::getTable('module') .' where id=?', [$this->module_id]);

            if (1 == $sql->getRows()) {
                $this->key = $sql->getValue('key');
            } else {
                $this->key = null;
            }
            assert('' !== $this->key);
        }

        return $this->key;
    }
}
