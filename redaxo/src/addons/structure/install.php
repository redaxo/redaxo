<?php

rex_sql_table::get(rex::getTable('article'))
    ->ensureColumn(new rex_sql_column('pid', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('parent_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('name', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('catname', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('catpriority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('startarticle', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('path', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('template_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('revision', 'int(10) unsigned'))
    ->setPrimaryKey('pid')
    ->ensureIndex(new rex_sql_index('find_articles', ['id', 'clang_id'], rex_sql_index::UNIQUE))
    ->ensureIndex(new rex_sql_index('clang_id', ['clang_id']))
    ->ensureIndex(new rex_sql_index('parent_id', ['parent_id']))
    ->removeIndex('id')
    ->ensure();

$sql = rex_sql::factory();
$sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article_slice set revision=0 where revision<1 or revision IS NULL');
$sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article set revision=0 where revision<1 or revision IS NULL');
