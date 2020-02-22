<?php

class rex_module {
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
    static public function forKey($module_key) {
        $sql = rex_sql::factory();
        $sql->setQuery('select id from rex_module where `key`=?', [$module_key]);

        if ($sql->getRows() == 1) {
            $module_id = $sql->getValue('id');

            return new self($module_id);
        }
        return null;
    }

    public function getModuleId() {
        return $this->module_id;
    }

    public function getKey() {
        if ($this->key === null) {
            $this->key = '';

            $sql = rex_sql::factory();
            $sql->setQuery('select `key` from rex_module where id=?', [$this->module_id]);

            if ($sql->getRows() == 1) {
                $this->key = $sql->getValue('key');
            }
        }

        return $this->key;
    }
}
