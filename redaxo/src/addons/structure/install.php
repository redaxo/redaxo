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

rex_sql_table::get(rex::getTable('article_slice_history'))
    ->ensureColumn(new rex_sql_column('id', 'int(10) unsigned', false, null, 'AUTO_INCREMENT'))
    ->ensureColumn(new rex_sql_column('slice_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('history_type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('history_date', 'datetime'))
    ->ensureColumn(new rex_sql_column('history_user', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('clang_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('ctype_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('priority', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('value1', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value2', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value3', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value4', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value5', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value6', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value7', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value8', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value9', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value10', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value11', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value12', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value13', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value14', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value15', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value16', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value17', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value18', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value19', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('value20', 'mediumtext'))
    ->ensureColumn(new rex_sql_column('media1', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media2', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media3', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media4', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media5', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media6', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media7', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media8', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media9', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('media10', 'varchar(255)', true))
    ->ensureColumn(new rex_sql_column('medialist1', 'text'))
    ->ensureColumn(new rex_sql_column('medialist2', 'text'))
    ->ensureColumn(new rex_sql_column('medialist3', 'text'))
    ->ensureColumn(new rex_sql_column('medialist4', 'text'))
    ->ensureColumn(new rex_sql_column('medialist5', 'text'))
    ->ensureColumn(new rex_sql_column('medialist6', 'text'))
    ->ensureColumn(new rex_sql_column('medialist7', 'text'))
    ->ensureColumn(new rex_sql_column('medialist8', 'text'))
    ->ensureColumn(new rex_sql_column('medialist9', 'text'))
    ->ensureColumn(new rex_sql_column('medialist10', 'text'))
    ->ensureColumn(new rex_sql_column('link1', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link2', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link3', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link4', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link5', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link6', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link7', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link8', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link9', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('link10', 'varchar(10)', true))
    ->ensureColumn(new rex_sql_column('linklist1', 'text'))
    ->ensureColumn(new rex_sql_column('linklist2', 'text'))
    ->ensureColumn(new rex_sql_column('linklist3', 'text'))
    ->ensureColumn(new rex_sql_column('linklist4', 'text'))
    ->ensureColumn(new rex_sql_column('linklist5', 'text'))
    ->ensureColumn(new rex_sql_column('linklist6', 'text'))
    ->ensureColumn(new rex_sql_column('linklist7', 'text'))
    ->ensureColumn(new rex_sql_column('linklist8', 'text'))
    ->ensureColumn(new rex_sql_column('linklist9', 'text'))
    ->ensureColumn(new rex_sql_column('linklist10', 'text'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('module_id', 'int(10) unsigned'))
    ->ensureGlobalColumns()
    ->ensureColumn(new rex_sql_column('revision', 'int(11)'))
    ->setPrimaryKey('id')
    ->ensureIndex(new rex_sql_index('snapshot', ['article_id', 'clang_id', 'revision', 'history_date']))
    ->ensure();
