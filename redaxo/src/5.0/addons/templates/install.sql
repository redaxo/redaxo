CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%template` (
  `id` int(11) NOT NULL  auto_increment,
  `label` varchar(255) NULL,
  `name` varchar(255) NULL,
  `content` text NULL,
  `active` tinyint(1) NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `attributes` text NULL,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;