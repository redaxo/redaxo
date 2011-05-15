<?php

if ($eventType == REX_A1_IMPORT_EVENT_PRE)
{

  $update = rex_sql::factory();
  // $update->setDebug();

  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."template` ADD `revision` INT NOT NULL;");

  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."action` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."article` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."article_slice` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."clang` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."media` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."media_category` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."module` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."module_action` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."user` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."template` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");

  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."article` DROP `label`, DROP `url`");

  $update->setQuery("UPDATE `". rex_core::getTablePrefix() ."article` SET `revision` = 0 WHERE `revision` IS NULL;");
  $update->setQuery("UPDATE `". rex_core::getTablePrefix() ."article_slice` SET `revision` = 0 WHERE `revision` IS NULL;");

  // add indizies
  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."article ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD UNIQUE INDEX `find_articles` (`id`, `clang`), ADD INDEX `re_id` (`re_id`);");
  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."article_slice ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD INDEX `re_article_slice_id` (`re_article_slice_id`), ADD INDEX `article_id` (`article_id`), ADD INDEX `find_slices` (`clang`, `article_id`);");
  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."media ADD INDEX `re_media_id` (`re_media_id`), ADD INDEX `category_id` (`category_id`);");
  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."media_category DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `re_id` (`re_id`);");
  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."module DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `category_id` (`category_id`);");
  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."user ADD UNIQUE INDEX `login` (`login`(50));");

  $update->setQuery("ALTER TABLE ". rex_core::getTablePrefix() ."62_type ADD UNIQUE INDEX `login` (`login`(50));");
  $update->setQuery("UPDATE ". rex_core::getTablePrefix() ."62_type set dbtype='text', dblength='0' where label='". rex_core::getTablePrefix() ."MEDIALIST_BUTTON' or label='". rex_core::getTablePrefix() ."LINKLIST_BUTTON'");

  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."62_params` CHANGE `validate` `validate` TEXT DEFAULT NULL");
  $update->setQuery("ALTER TABLE `". rex_core::getTablePrefix() ."62_params` ADD `restrictions` TEXT NOT NULL AFTER `validate`");
  // $update->setQuery("");

}

?>