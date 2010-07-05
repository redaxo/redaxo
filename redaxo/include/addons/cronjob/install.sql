CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%630_cronjobs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) default NULL,
  `type` varchar(255) default NULL,
  `parameters` text default NULL,
  `interval` varchar(255) default NULL,
  `nexttime` int(11) default 0,
  `environment` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `createdate` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;