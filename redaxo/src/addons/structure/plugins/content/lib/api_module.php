<?php

class rex_module
{
    /**
     * @var int
     */
    private $module_id;
    /**
     * @var string
     */
    private $key;

    public function __construct($module_id)
    {
        $this->module_id = $module_id;
    }

    /**
     * @param string $module_key
     *
     * @return self|null
     */
    public static function forKey($module_key)
    {
        $sql = rex_sql::factory();
        $sql->setQuery('select id from rex_module where `key`=?', [$module_key]);

        if (1 == $sql->getRows()) {
            $module_id = $sql->getValue('id');

            $module = new self($module_id);
            $module->key = $module_key;
            return $module;
        }
        return null;
    }

    public function getModuleId()
    {
        return $this->module_id;
    }

    public function getKey()
    {
        if (null === $this->key) {
            $this->key = '';

            $sql = rex_sql::factory();
            $sql->setQuery('select `key` from rex_module where id=?', [$this->module_id]);

            if (1 == $sql->getRows()) {
                $this->key = $sql->getValue('key');
            }
        }

        return $this->key;
    }
}
