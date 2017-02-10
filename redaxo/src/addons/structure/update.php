<?php

/** @var rex_addon $this */

if ($this->getPlugin('history')->isAvailable() && rex_string::versionCompare($this->getVersion(), '2.3.0-dev', '<')) {
    rex_sql_table::get(rex_article_slice_history::getTable())
        ->ensureColumn(new rex_sql_column('history_user', 'varchar(255)'))
        ->alter();
}
