<?php

/**
 * Cronjob Addon - Plugin optimize_tables.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 *
 * @package redaxo\cronjob\optimize-tables
 */

class rex_cronjob_optimize_tables extends rex_cronjob
{
    public function execute()
    {
        $tables = rex_sql::factory()->getTables(rex::getTablePrefix());
        if (is_array($tables) && !empty($tables)) {
            $sql = rex_sql::factory();
            // $sql->setDebug();
            try {
                $sql->setQuery('OPTIMIZE TABLE ' . implode(', ', array_map([$sql, 'escapeIdentifier'], $tables)));
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
