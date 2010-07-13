CREATE TABLE `%TABLE_PREFIX%62_params` (
  `field_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `prior` int(10) unsigned NOT NULL,
  `attributes` text NOT NULL,
  `type` int(10) unsigned default NULL,
  `default` varchar(255) NOT NULL,
  `params` text default NULL,
  `validate` text NULL,
  `restrictions` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `updatedate` int(11) NOT NULL,
  PRIMARY KEY  (`field_id`),
  UNIQUE KEY `name` (`name`)
);

CREATE TABLE `%TABLE_PREFIX%62_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) default NULL,
  `dbtype` varchar(255) NOT NULL,
  `dblength` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM ;

INSERT INTO %TABLE_PREFIX%62_type VALUES (1,  'text', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (2,  'textarea', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (3,  'select', 'varchar', 255);
INSERT INTO %TABLE_PREFIX%62_type VALUES (4,  'radio', 'varchar', 255);
INSERT INTO %TABLE_PREFIX%62_type VALUES (5,  'checkbox', 'varchar', 255);
INSERT INTO %TABLE_PREFIX%62_type VALUES (10, 'date', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (13, 'time', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (11, 'datetime', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (12, 'legend', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (6,  'REX_MEDIA_BUTTON', 'varchar', 255);
INSERT INTO %TABLE_PREFIX%62_type VALUES (7,  'REX_MEDIALIST_BUTTON', 'text', 0);
INSERT INTO %TABLE_PREFIX%62_type VALUES (8,  'REX_LINK_BUTTON', 'varchar', 255);
INSERT INTO %TABLE_PREFIX%62_type VALUES (9,  'REX_LINKLIST_BUTTON', 'text', 0);

INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('1','translate:pool_file_description','med_description','1','','2','','','','','admin','1189343866','admin','1189344596');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('2','translate:pool_file_copyright','med_copyright','2','','1','','','','','admin','1189343877','admin','1189344617');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('3','translate:online_from','art_online_from','1','','10','','','','','admin','1189344934','admin','1189344934');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('4','translate:online_to','art_online_to','2','','10','','','','','admin','1189344947','admin','1189344947');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('5','translate:description','art_description','3','','2','','','','','admin','1189345025','admin','1189345025');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('6','translate:keywords','art_keywords','4','','2','','','','','admin','1189345068','admin','1189345068');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('7','translate:metadata_image','art_file','5','','6','','','','','admin','1189345109','admin','1189345109');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('8','translate:teaser','art_teaser','6','','5','','','','','admin','1189345182','admin','1189345182');
INSERT INTO `%TABLE_PREFIX%62_params` VALUES ('9','translate:header_article_type','art_type_id','7','size=1','3','','Standard|Zugriff für alle','','','admin','1191963797','admin','1191964038');

ALTER TABLE `%TABLE_PREFIX%article` ADD `art_online_from` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_online_to` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_description` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_keywords` TEXT;
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_file` VARCHAR(255);
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_teaser` VARCHAR(255);
ALTER TABLE `%TABLE_PREFIX%article` ADD `art_type_id` VARCHAR(255);

ALTER TABLE `%TABLE_PREFIX%file` ADD `med_description` TEXT;
ALTER TABLE `%TABLE_PREFIX%file` ADD `med_copyright` TEXT;