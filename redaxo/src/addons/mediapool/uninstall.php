<?php

/**
 * Mediapool Addon.
 *
 * @author redaxo
 *
 * @package redaxo5
 */

rex_delete_cache();

rex_sql_table::get(rex::getTable('media'))->drop();
rex_sql_table::get(rex::getTable('media_category'))->drop();
