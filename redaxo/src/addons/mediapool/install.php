<?php

/**
 * Mediapool Addon.
 *
 * @author redaxo
 *
 * @package redaxo5
 */

rex_delete_cache();

rex_sql_table::get(rex::getTable('media'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('category_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('attributes', 'text', true))
    ->ensureColumn(new rex_sql_column('filetype', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('filename', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('originalname', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('filesize', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('width', 'int(10) unsigned', true))
    ->ensureColumn(new rex_sql_column('height', 'int(10) unsigned', true))
    ->ensureColumn(new rex_sql_column('title', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->ensureIndex(new rex_sql_index('category_id', ['category_id']))
    ->ensure();

rex_sql_table::get(rex::getTable('media_category'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('parent_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('path', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('attributes', 'text', true))
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->ensureIndex(new rex_sql_index('parent_id', ['parent_id']))
    ->ensure();
