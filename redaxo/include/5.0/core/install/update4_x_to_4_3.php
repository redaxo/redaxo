<?php

if ($eventType == REX_A1_IMPORT_EVENT_PRE)
{

  $update = rex_sql::factory();
  // $update->setDebug();
  
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."template` ADD `revision` INT NOT NULL;");
  
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."action` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."article` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."article_slice` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."clang` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."file` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."file_category` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."module` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."module_action` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."user` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."template` CHANGE `revision` `revision` INT( 11 ) NOT NULL DEFAULT '0';");
  
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."article` DROP `label`, DROP `url`");
  
  $update->setQuery("UPDATE `". $REX['TABLE_PREFIX'] ."article` SET `revision` = 0 WHERE `revision` IS NULL;");
  $update->setQuery("UPDATE `". $REX['TABLE_PREFIX'] ."article_slice` SET `revision` = 0 WHERE `revision` IS NULL;");
  
  // add indizies
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."article ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD UNIQUE INDEX `find_articles` (`id`, `clang`), ADD INDEX `re_id` (`re_id`);");
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."article_slice ADD INDEX `id` (`id`), ADD INDEX `clang` (`clang`), ADD INDEX `re_article_slice_id` (`re_article_slice_id`), ADD INDEX `article_id` (`article_id`), ADD INDEX `find_slices` (`clang`, `article_id`);");
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."file ADD INDEX `re_file_id` (`re_file_id`), ADD INDEX `category_id` (`category_id`);");
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."file_category DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `re_id` (`re_id`);");
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."module DROP PRIMARY KEY, ADD PRIMARY KEY (`id`), ADD INDEX `category_id` (`category_id`);");
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."user ADD UNIQUE INDEX `login` (`login`(50));");
  
  $update->setQuery("ALTER TABLE ". $REX['TABLE_PREFIX'] ."62_type ADD UNIQUE INDEX `login` (`login`(50));");
  $update->setQuery("UPDATE ". $REX['TABLE_PREFIX'] ."62_type set dbtype='text', dblength='0' where label='". $REX['TABLE_PREFIX'] ."MEDIALIST_BUTTON' or label='". $REX['TABLE_PREFIX'] ."LINKLIST_BUTTON'");
  
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."62_params` CHANGE `validate` `validate` TEXT DEFAULT NULL");
  $update->setQuery("ALTER TABLE `". $REX['TABLE_PREFIX'] ."62_params` ADD `restrictions` TEXT NOT NULL AFTER `validate`");
  // $update->setQuery("");

}

?>