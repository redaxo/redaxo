<?php

/** @var rex_addon $this */

if ($this->getPlugin('content')->isInstalled() && rex_string::versionCompare($this->getVersion(), '2.3.0', '<')) {
    rex_sql_table::get(rex::getTable('template'))
        ->ensureColumn(new rex_sql_column('content', 'mediumtext', true))
        ->alter();
    rex_sql_table::get(rex::getTable('module'))
        ->ensureColumn(new rex_sql_column('input', 'mediumtext', true))
        ->ensureColumn(new rex_sql_column('output', 'mediumtext', true))
        ->alter();
}

if ($this->getPlugin('history')->isInstalled() && rex_string::versionCompare($this->getVersion(), '2.3.0', '<')) {
    rex_sql_table::get(rex::getTable('article_slice_history'))
        ->ensureColumn(new rex_sql_column('history_user', 'varchar(255)'))
        ->alter();
}
