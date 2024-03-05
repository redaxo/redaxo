<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;

class rex_cronjob_optimize_tables extends rex_cronjob
{
    public function execute()
    {
        $tables = Sql::factory()->getTables(Core::getTablePrefix());
        if (!empty($tables)) {
            $sql = Sql::factory();
            // $sql->setDebug();
            try {
                $sql->setQuery('OPTIMIZE TABLE ' . implode(', ', array_map($sql->escapeIdentifier(...), $tables)));
                return true;
            } catch (rex_sql_exception $e) {
                $this->setMessage($e->getMessage());
                return false;
            }
        }
        return false;
    }

    public function getTypeName()
    {
        return rex_i18n::msg('cronjob_optimize_tables');
    }
}
