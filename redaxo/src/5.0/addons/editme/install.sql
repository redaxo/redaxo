CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%em_field` (
  `id` int(11) NOT NULL auto_increment,
  `table_name` varchar(255) NOT NULL,
  `type_name` varchar(255) NOT NULL,
  `type_id` varchar(255) NOT NULL,
  `prio` varchar(255) NOT NULL,
  `list_hidden` TINYINT NOT NULL,
  `f1` text NOT NULL,
  `f2` text NOT NULL,
  `f3` text NOT NULL,
  `f4` text NOT NULL,
  `f5` text NOT NULL,
  `f6` text NOT NULL,
  `f7` text NOT NULL,
  `f8` text NOT NULL,
  `f9` text NOT NULL,
  `search` TINYINT NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%em_table` (
  `id` int(11) NOT NULL auto_increment,
  `status` tinyint(4) NOT NULL,
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `list_amount` TINYINT UNSIGNED NOT NULL DEFAULT '50',
  `prio` TINYINT NOT NULL,
  `search` TINYINT NOT NULL,
  `hidden` TINYINT NOT NULL,
  `export` TINYINT NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%em_relation` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `source_table` VARCHAR( 255 ) NOT NULL ,
  `source_name` VARCHAR( 255 ) NOT NULL ,
  `source_id` INT NOT NULL ,
  `target_table` VARCHAR( 255 ) NOT NULL ,
  `target_id` INT NOT NULL
);