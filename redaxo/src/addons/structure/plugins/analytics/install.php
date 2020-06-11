<?php

rex_sql_table::get(rex::getTable('webvitals'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('uri', 'text'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)'))
    ->ensureColumn(new rex_sql_column('cls', 'int(11)', true, null, null, 'cumulative layout shift'))
    ->ensureColumn(new rex_sql_column('fid', 'int(11)', true, null, null, 'first input delay'))
    ->ensureColumn(new rex_sql_column('lcp', 'int(11)', true, null, null, 'largest contentful paint'))
    ->ensureColumn(new rex_sql_column('ttfb', 'int(11)', true, null, null, 'time to first byte'))
    ->setPrimaryKey('id')
    ->ensure();

rex_sql_table::get(rex::getTable('webvitals_95p'))
    ->ensureColumn(new rex_sql_column('id', 'int(11)', false, null, 'auto_increment'))
    ->ensureColumn(new rex_sql_column('uri', 'text'))
    ->ensureColumn(new rex_sql_column('urihash', 'varchar(255)'), false, null, null, 'indexed representation of the uri')
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)'))
    ->ensureColumn(new rex_sql_column('cls', 'int(11)', true, null, null, 'cumulative layout shift'))
    ->ensureColumn(new rex_sql_column('fid', 'int(11)', true, null, null, 'first input delay'))
    ->ensureColumn(new rex_sql_column('lcp', 'int(11)', true, null, null, 'largest contentful paint'))
    ->ensureColumn(new rex_sql_column('ttfb', 'int(11)', true, null, null, 'time to first byte'))
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('urihash', ['urihash'], rex_sql_index::UNIQUE))
    ->ensure();

