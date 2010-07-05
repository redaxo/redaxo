CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%a724_frau_schultze` (
  `pid` INT(11) unsigned NOT NULL auto_increment,
  `article_id` INT(11) NOT NULL,
  `clang` INT(11) NOT NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '0',
  
  `name` VARCHAR(255) DEFAULT NULL,
  `url` TEXT DEFAULT NULL,
  `redirect` TINYINT(1) NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL,
  `url_table` varchar(255) NOT NULL,
  `url_table_parameters` text NOT NULL,
  
  `createdate` INT(11) NOT NULL,
  `createuser` VARCHAR(255) NOT NULL,
  `updatedate` INT(11) NOT NULL,
  `updateuser` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`pid`)
) TYPE=MyISAM ;