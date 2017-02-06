<?php

/** @var rex_addon $this */

if (rex_string::versionCompare($this->getVersion(), '1.0.1', '<')) {
    rex_sql_table::get(rex_article_slice_history::getTable())
    ->ensureColumn(new rex_sql_column('history_user', 'varchar(255)'))
    ->alter();
}
