<?php

/**
 * Cronjob Addon.
 *
 * @author gharlan[at]web[dot]de Gregor Harlan
 */

$addon = rex_addon::get('cronjob');

rex_dir::delete($addon->getDataPath());

rex_sql_table::get(rex::getTable('cronjob'))->drop();
