<?php

rex_sql_util::importDump($this->getPath('_install.sql'));

// version 2.3.0
rex_sql_table::get(rex::getTable('article_slice_history'))
    ->ensureColumn(new rex_sql_column('history_user', 'varchar(255)'))
    ->alter();
