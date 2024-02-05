<?php

rex_sql_table::get(rex::getTable('action'))->drop();
rex_sql_table::get(rex::getTable('article'))->drop();
rex_sql_table::get(rex::getTable('article_slice'))->drop();
rex_sql_table::get(rex::getTable('article_slice_history'))->drop();
rex_sql_table::get(rex::getTable('module'))->drop();
rex_sql_table::get(rex::getTable('module_action'))->drop();
rex_sql_table::get(rex::getTable('template'))->drop();
