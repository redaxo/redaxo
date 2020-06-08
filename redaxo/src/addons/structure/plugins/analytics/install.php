<?php

rex_sql_table::get(rex::getTable('webvitals'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('uri', 'text'))
    ->ensureColumn(new rex_sql_column('cls', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('fid', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('lcp', 'int(11)', true))
    ->ensureColumn(new rex_sql_column('ttfb', 'int(11)', true))
    ->setPrimaryKey('id')
    ->ensure();
