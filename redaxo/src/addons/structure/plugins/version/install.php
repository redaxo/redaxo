<?php

/**
 * Version.
 *
 * @author jan@kristinus.de
 *
 * @package redaxo5
 */

$create_sql = rex_sql::factory();
$create_sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article_slice set revision=0 where revision<1 or revision IS NULL');
$create_sql->setQuery('UPDATE ' . rex::getTablePrefix() . 'article set revision=0 where revision<1 or revision IS NULL');

// $create_sql->setQuery("ALTER TABLE `rex_template` ADD `revision` INT NOT NULL DEFAULT '0'");
// $create_sql->setQuery("ALTER TABLE `rex_template` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0'");
