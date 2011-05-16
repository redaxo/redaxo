<?php

// TODO Adjust statements to final r5 db structure 


if ($eventType == REX_A1_IMPORT_EVENT_PRE)
{

  $update = rex_sql::factory();
  // $update->setDebug();

  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."template` ADD `revision` INT NOT NULL;");

  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."action` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."article` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."article_slice` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."clang` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."media` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."media_category` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."module` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."module_action` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."user` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."template` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");

  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."article` DROP `label`, DROP `url`");

  $update->setQuery("UPDATE `". rex::getTablePrefix() ."article` SET `revision` = 0 WHERE `revision` IS NULL;");
  $update->setQuery("UPDATE `". rex::getTablePrefix() ."article_slice` SET `revision` = 0 WHERE `revision` IS NULL;");

  // add indizies
  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."article ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD UNIQUE INDEX `find_articles` (`id`, `clang`), ADD INDEX `re_id` (`re_id`);");
  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."article_slice ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD INDEX `re_article_slice_id` (`re_article_slice_id`), ADD INDEX `article_id` (`article_id`), ADD INDEX `find_slices` (`clang`, `article_id`);");
  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."media ADD INDEX `re_media_id` (`re_media_id`), ADD INDEX `category_id` (`category_id`);");
  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."media_category DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `re_id` (`re_id`);");
  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."module DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `category_id` (`category_id`);");
  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."user ADD UNIQUE INDEX `login` (`login`(50));");

  $update->setQuery("ALTER TABLE ". rex::getTablePrefix() ."62_type ADD UNIQUE INDEX `login` (`login`(50));");
  $update->setQuery("UPDATE ". rex::getTablePrefix() ."62_type set dbtype='text', dblength='0' where label='". rex::getTablePrefix() ."MEDIALIST_BUTTON' or label='". rex::getTablePrefix() ."LINKLIST_BUTTON'");

  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."62_params` CHANGE `validate` `validate` TEXT DEFAULT NULL");
  $update->setQuery("ALTER TABLE `". rex::getTablePrefix() ."62_params` ADD `restrictions` TEXT NOT NULL AFTER `validate`");
  // $update->setQuery("");

}

?>