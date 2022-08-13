<?php

/**
 * MetaForm Addon.
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 */

$curDir = __DIR__;
require_once $curDir . '/extensions/extension_cleanup.php';

rex_metainfo_cleanup(['force' => true]);

rex_sql_table::get(rex::getTable('metainfo_field'))->drop();
rex_sql_table::get(rex::getTable('metainfo_type'))->drop();
