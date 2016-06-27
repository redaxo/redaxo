<?php

rex_sql_util::importDump($this->getPath('_install.sql'));

$sql = rex_sql::factory();
$sql->setQuery('SELECT 1 FROM '.rex::getTable('template').' LIMIT 1');
if (!$sql->getRows()) {
    $sql
        ->setTable(rex::getTable('template'))
        ->setValue('id', 1)
        ->setValue('name', 'Default')
        ->setValue('content', 'REX_ARTICLE[]')
        ->setValue('active', 1)
        ->setValue('attributes', '{"ctype":[],"modules":{"1":{"all":"1"}},"categories":{"all":"1"}}')
        ->setRawValue('createdate', 'NOW()')
        ->setRawValue('updatedate', 'NOW()')
        ->insert();
}
