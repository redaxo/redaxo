<?php

rex_sql_table::get(rex::getTable('article'))->drop();
rex_sql_table::get(rex::getTable('article_slice_history'))->drop();
