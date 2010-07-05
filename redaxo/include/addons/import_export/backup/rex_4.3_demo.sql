## Redaxo Database Dump Version 4
## Prefix rex_
## charset iso-8859-1

DROP TABLE IF EXISTS `rex_62_params`;
CREATE TABLE `rex_62_params` (
  `field_id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `name` varchar(255) default NULL,
  `prior` int(10) unsigned NOT NULL,
  `attributes` text NOT NULL,
  `type` int(10) unsigned default NULL,
  `default` varchar(255) NOT NULL,
  `params` text,
  `validate` text,
  `restrictions` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `updatedate` int(11) NOT NULL,
  PRIMARY KEY  (`field_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_62_params` WRITE;
/*!40000 ALTER TABLE `rex_62_params` DISABLE KEYS */;
INSERT INTO `rex_62_params` VALUES 
  (1,'translate:pool_file_description','med_description',1,'',2,'','','','','admin',1189343866,'admin',1189344596),
  (2,'translate:pool_file_copyright','med_copyright',2,'',1,'','','','','admin',1189343877,'admin',1189344617),
  (3,'translate:online_from','art_online_from',1,'',10,'','','','','admin',1189344934,'admin',1189344934),
  (4,'translate:online_to','art_online_to',2,'',10,'','','','','admin',1189344947,'admin',1189344947),
  (5,'translate:description','art_description',3,'',2,'','','','','admin',1189345025,'admin',1189345025),
  (6,'translate:keywords','art_keywords',4,'',2,'','','','','admin',1189345068,'admin',1189345068),
  (7,'translate:metadata_image','art_file',7,'',6,'','','','','admin',1189345109,'admin',1237995585),
  (8,'translate:teaser','art_teaser',5,'',5,'','','','','admin',1189345182,'admin',1189345182),
  (9,'translate:header_article_type','art_type_id',6,'size=1',3,'','Standard|Zugriff für alle','','','admin',1191963797,'admin',1191964038),
  (10,'Zugriffsrechte','',1,'',3,'','0:Alle|-1:Nur nicht Eingeloggte|1:Nur Eingeloggte|2:Nur Moderatoren und Admins|3:Nur Admins','','','admin',1237383022,'',0);
/*!40000 ALTER TABLE `rex_62_params` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_62_type`;
CREATE TABLE `rex_62_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `label` varchar(255) default NULL,
  `dbtype` varchar(255) NOT NULL,
  `dblength` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_62_type` WRITE;
/*!40000 ALTER TABLE `rex_62_type` DISABLE KEYS */;
INSERT INTO `rex_62_type` VALUES 
  (1,'text','varchar',255),
  (2,'textarea','text',0),
  (3,'select','varchar',255),
  (4,'radio','varchar',255),
  (5,'checkbox','varchar',255),
  (10,'date','varchar',255),
  (11,'datetime','varchar',255),
  (6,'REX_MEDIA_BUTTON','varchar',255),
  (7,'REX_MEDIALIST_BUTTON','text',0),
  (8,'REX_LINK_BUTTON','varchar',255),
  (9,'REX_LINKLIST_BUTTON','text',0),
  (12,'legend','varchar',255);
/*!40000 ALTER TABLE `rex_62_type` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_679_type_effects`;
CREATE TABLE `rex_679_type_effects` (
  `id` int(11) NOT NULL auto_increment,
  `type_id` int(11) NOT NULL,
  `effect` varchar(255) NOT NULL,
  `parameters` text NOT NULL,
  `prior` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_679_type_effects` WRITE;
/*!40000 ALTER TABLE `rex_679_type_effects` DISABLE KEYS */;
INSERT INTO `rex_679_type_effects` VALUES 
  (1,1,'resize','a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"200\";s:24:\"rex_effect_resize_height\";s:3:\"200\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}',1,0,'',0,''),
  (2,2,'resize','a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"600\";s:24:\"rex_effect_resize_height\";s:3:\"600\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}',1,0,'',0,''),
  (3,3,'resize','a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:2:\"80\";s:24:\"rex_effect_resize_height\";s:2:\"80\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}',1,0,'',0,''),
  (4,4,'resize','a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"246\";s:24:\"rex_effect_resize_height\";s:3:\"246\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}',1,0,'',0,''),
  (5,5,'resize','a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"246\";s:24:\"rex_effect_resize_height\";s:3:\"246\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}',1,0,'',0,''),
  (6,6,'resize','a:6:{s:15:\"rex_effect_crop\";a:5:{s:21:\"rex_effect_crop_width\";s:0:\"\";s:22:\"rex_effect_crop_height\";s:0:\"\";s:28:\"rex_effect_crop_offset_width\";s:0:\"\";s:29:\"rex_effect_crop_offset_height\";s:0:\"\";s:24:\"rex_effect_crop_position\";s:13:\"middle_center\";}s:22:\"rex_effect_filter_blur\";a:3:{s:29:\"rex_effect_filter_blur_amount\";s:2:\"80\";s:29:\"rex_effect_filter_blur_radius\";s:1:\"8\";s:32:\"rex_effect_filter_blur_threshold\";s:1:\"3\";}s:25:\"rex_effect_filter_sharpen\";a:3:{s:32:\"rex_effect_filter_sharpen_amount\";s:2:\"80\";s:32:\"rex_effect_filter_sharpen_radius\";s:3:\"0.5\";s:35:\"rex_effect_filter_sharpen_threshold\";s:1:\"3\";}s:15:\"rex_effect_flip\";a:1:{s:20:\"rex_effect_flip_flip\";s:1:\"X\";}s:23:\"rex_effect_insert_image\";a:5:{s:34:\"rex_effect_insert_image_brandimage\";s:0:\"\";s:28:\"rex_effect_insert_image_hpos\";s:5:\"right\";s:28:\"rex_effect_insert_image_vpos\";s:6:\"bottom\";s:33:\"rex_effect_insert_image_padding_x\";s:3:\"-10\";s:33:\"rex_effect_insert_image_padding_y\";s:3:\"-10\";}s:17:\"rex_effect_resize\";a:4:{s:23:\"rex_effect_resize_width\";s:3:\"250\";s:24:\"rex_effect_resize_height\";s:0:\"\";s:23:\"rex_effect_resize_style\";s:7:\"maximum\";s:31:\"rex_effect_resize_allow_enlarge\";s:11:\"not_enlarge\";}}',1,0,'',0,'');
/*!40000 ALTER TABLE `rex_679_type_effects` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_679_types`;
CREATE TABLE `rex_679_types` (
  `id` int(11) NOT NULL auto_increment,
  `status` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_679_types` WRITE;
/*!40000 ALTER TABLE `rex_679_types` DISABLE KEYS */;
INSERT INTO `rex_679_types` VALUES 
  (1,1,'rex_mediapool_detail','Zur Darstellung von Bildern in der Detailansicht im Medienpool'),
  (2,1,'rex_mediapool_maximized','Zur Darstellung von Bildern im Medienpool wenn maximiert'),
  (3,1,'rex_mediapool_preview','Zur Darstellung der Vorschaubilder im Medienpool'),
  (4,1,'rex_mediabutton_preview','Zur Darstellung der Vorschaubilder in REX_MEDIA_BUTTON[]s'),
  (5,1,'rex_medialistbutton_preview','Zur Darstellung der Vorschaubilder in REX_MEDIALIST_BUTTON[]s'),
  (6,1,'gallery_overview','Zur Anzeige der Screenshot-Gallerie');
/*!40000 ALTER TABLE `rex_679_types` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_action`;
CREATE TABLE `rex_action` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `preview` text NOT NULL,
  `presave` text NOT NULL,
  `postsave` text NOT NULL,
  `previewmode` tinyint(4) NOT NULL default '0',
  `presavemode` tinyint(4) NOT NULL default '0',
  `postsavemode` tinyint(4) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updateuser` varchar(255) NOT NULL,
  `updatedate` int(11) NOT NULL default '0',
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `rex_article`;
CREATE TABLE `rex_article` (
  `pid` int(11) NOT NULL auto_increment,
  `id` int(11) NOT NULL default '0',
  `re_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `catname` varchar(255) NOT NULL,
  `catprior` int(11) NOT NULL default '0',
  `attributes` text NOT NULL,
  `startpage` tinyint(1) NOT NULL default '0',
  `prior` int(11) NOT NULL default '0',
  `path` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `template_id` int(11) NOT NULL default '0',
  `clang` int(11) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL default '0',
  `art_online_from` varchar(255) default NULL,
  `art_online_to` varchar(255) default NULL,
  `art_description` text,
  `art_keywords` text,
  `art_file` varchar(255) default NULL,
  `art_teaser` varchar(255) default NULL,
  `art_type_id` varchar(255) default NULL,
  PRIMARY KEY  (`pid`),
  UNIQUE KEY `find_articles` (`id`,`clang`),
  KEY `id` (`id`),
  KEY `clang` (`clang`),
  KEY `re_id` (`re_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_article` WRITE;
/*!40000 ALTER TABLE `rex_article` DISABLE KEYS */;
INSERT INTO `rex_article` VALUES 
  (1,1,0,'Home','Home',1,'',1,1,'|',1,1192226202,1192234473,1,0,'admin','admin',0,'','','','','','',''),
  (2,2,0,'Team','Team',2,'',1,1,'|',1,1192226377,1273067553,1,0,'admin','admin',0,'','','','','','',''),
  (3,3,0,'System','System',3,'',1,1,'|',1,1174487175,1181732593,1,0,'admin','admin',0,'','','','','','',''),
  (4,4,0,'Erste Schritte','Erste Schritte',4,'',1,1,'|',1,1174487184,1237973283,1,0,'admin','admin',0,'','','','','','',''),
  (5,5,0,'FAQ','FAQ',5,'',1,1,'|',1,1237372127,1237970781,1,0,'admin','admin',0,'','','','','','',''),
  (6,6,0,'Kontakt / Impressum','Kontakt / Impressum',6,'',1,1,'|',1,1174487203,1237976151,1,0,'admin','admin',0,'','','','','','',''),
  (8,8,3,'Was ist REDAXO','Was ist REDAXO',1,'',1,1,'|3|',1,1174488327,1237975038,1,0,'admin','admin',0,'','','','','','',''),
  (9,9,3,'Für wen ist REDAXO','Für wen ist REDAXO',2,'',1,1,'|3|',1,1174488348,1237975175,1,0,'admin','admin',0,'','','','','','',''),
  (10,10,3,'Features','Features',3,'',1,1,'|3|',1,1174489132,1237975465,1,0,'admin','admin',0,'','','','','','',''),
  (11,11,3,'Screenshots','Screenshots',4,'',1,1,'|3|',1,1174489141,1237383922,1,0,'admin','admin',0,'','','','','','',''),
  (12,12,4,'Doku','Doku',2,'',1,1,'|4|',1,1174489168,1237973900,1,0,'admin','admin',0,'','','','','','',''),
  (13,13,4,'Wiki','Wiki',3,'',1,1,'|4|',1,1174489174,1237973233,1,0,'admin','admin',0,'','','','','','',''),
  (14,14,4,'Forum','Forum',4,'',1,1,'|4|',1,1174489181,1237973953,1,0,'admin','admin',0,'','','','','','',''),
  (15,15,5,'Was ist das Besondere an REDAXO?','FAQ',0,'',0,2,'|5|',1,1174489216,1237975651,1,0,'admin','admin',0,'','','','','','',''),
  (16,16,4,'REDAXO','REDAXO',1,'',1,1,'|4|',1,1179325162,1237973876,1,0,'admin','admin',0,'','','','','','',''),
  (17,17,5,'Was sollte einen dazu bewegen, REDAXO zu nutzen?','FAQ',0,'',0,3,'|5|',1,1189527244,1237970781,1,0,'admin','admin',0,'','','','','','',''),
  (18,18,5,'Wann wird der Einsatz von REDAXO empfohlen?','FAQ',0,'',0,4,'|5|',1,1189527313,1237975927,1,0,'admin','admin',0,'','','','','','',''),
  (19,19,5,'Wie viele Internetpräsentationen wurden bereits mit REDAXO erstellt?','FAQ',0,'',0,5,'|5|',1,1189527360,1237976010,1,0,'admin','admin',0,'','','','','','',''),
  (20,20,5,'Welche Kenntnisse brauche ich, um mit REDAXO arbeiten zu können?','FAQ',0,'',0,6,'|5|',1,1189527486,1237973721,1,0,'admin','admin',0,'','','','','','','');
/*!40000 ALTER TABLE `rex_article` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_article_slice`;
CREATE TABLE `rex_article_slice` (
  `id` int(11) NOT NULL auto_increment,
  `clang` int(11) NOT NULL default '0',
  `ctype` int(11) NOT NULL default '0',
  `re_article_slice_id` int(11) NOT NULL default '0',
  `value1` text,
  `value2` text,
  `value3` text,
  `value4` text,
  `value5` text,
  `value6` text,
  `value7` text,
  `value8` text,
  `value9` text,
  `value10` text,
  `value11` text,
  `value12` text,
  `value13` text,
  `value14` text,
  `value15` text,
  `value16` text,
  `value17` text,
  `value18` text,
  `value19` text,
  `value20` text,
  `file1` varchar(255) default NULL,
  `file2` varchar(255) default NULL,
  `file3` varchar(255) default NULL,
  `file4` varchar(255) default NULL,
  `file5` varchar(255) default NULL,
  `file6` varchar(255) default NULL,
  `file7` varchar(255) default NULL,
  `file8` varchar(255) default NULL,
  `file9` varchar(255) default NULL,
  `file10` varchar(255) default NULL,
  `filelist1` text,
  `filelist2` text,
  `filelist3` text,
  `filelist4` text,
  `filelist5` text,
  `filelist6` text,
  `filelist7` text,
  `filelist8` text,
  `filelist9` text,
  `filelist10` text,
  `link1` varchar(10) default NULL,
  `link2` varchar(10) default NULL,
  `link3` varchar(10) default NULL,
  `link4` varchar(10) default NULL,
  `link5` varchar(10) default NULL,
  `link6` varchar(10) default NULL,
  `link7` varchar(10) default NULL,
  `link8` varchar(10) default NULL,
  `link9` varchar(10) default NULL,
  `link10` varchar(10) default NULL,
  `linklist1` text,
  `linklist2` text,
  `linklist3` text,
  `linklist4` text,
  `linklist5` text,
  `linklist6` text,
  `linklist7` text,
  `linklist8` text,
  `linklist9` text,
  `linklist10` text,
  `php` text,
  `html` text,
  `article_id` int(11) NOT NULL default '0',
  `modultyp_id` int(11) NOT NULL default '0',
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `next_article_slice_id` int(11) default NULL,
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`,`re_article_slice_id`,`article_id`,`modultyp_id`),
  KEY `id` (`id`),
  KEY `clang` (`clang`),
  KEY `re_article_slice_id` (`re_article_slice_id`),
  KEY `article_id` (`article_id`),
  KEY `find_slices` (`clang`,`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_article_slice` WRITE;
/*!40000 ALTER TABLE `rex_article_slice` DISABLE KEYS */;
INSERT INTO `rex_article_slice` VALUES 
  (1,0,1,0,'h1. Internet Professionell lobt REDAXO\r\n\r\n\"Mit kaum einer anderen Redaktionssoftware ist es so mühelos möglich, wirklich valide und barrierefreie Websites zu erstellen. Gerade die extreme Anpassungsfähigkeit an die verschiedenen Bedürfnisse ist eine der großen Stärken dieses Redaktionssystems.\"\r\n\r\n\"Dank des Cachings und des insgesamt sehr schlanken Cores (1,5 MB) sind REDAXO-Websites normalerweise sehr schnell. Im Vergleich zu anderen Content-Management-Systemen beeindruckt bei REDAXO vor allem die Flexibilität und Anpassungsfähigkeit.\"','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',1,3,1181732140,1192234473,'admin','admin',0,0),
  (2,0,1,0,'An dieser Stelle möchten wir auch einmal Danke für die vielen Anregungen, Kritiken, Ideen, Bugmeldungen, Wünsche usw. sagen:','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,3,1181732193,0,'admin','',0,0),
  (3,0,1,2,'Das REDAXO Team','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,1,1181732232,1237973316,'admin','admin',0,0),
  (4,0,1,3,'Jan Kristinus\r\n\"www.yakamara.de\":http://www.yakamara.de','Jan Kristinus','','','','','','','l','','','','','','','','','','','','jan.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1181732266,1237372210,'admin','admin',0,0),
  (5,0,1,4,'Markus Staab\r\n\"www.redaxo.de\":http://www.redaxo.de','Markus Staab','','','','','','','l','','','','','','','','','','','','markus.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1181732302,1237372201,'admin','admin',0,0),
  (7,0,1,5,'Thomas Blum\r\n\"www.blumbeet.com\":http://www.blumbeet.com','Thomas Blum','','','','','','','l','','','','','','','','','','','','thomas.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1181732370,1237372223,'admin','admin',0,0),
  (12,0,1,0,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',8,'','','','','','','','','','','','','','','','','','','','','',3,8,1181732593,0,'admin','',0,0),
  (13,0,1,0,'Was ist REDAXO','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',8,1,1181732633,1237973407,'admin','admin',0,0),
  (15,0,1,13,'h2. Was ist REDAXO\r\n\r\nREDAXO ist ein Content Management System für individuelle, vielfältige und flexible Web-Lösungen.\r\n\r\nh2. Merkmale:\r\n\r\n* Trennung von Inhalt und Layout mittels Templates\r\n* Die Verwaltung von mehrsprachigen Webseiten ist gegeben\r\n* Der Inhalt setzt sich aus verschiedenen Modulen zusammen\r\n* Keine Grenzen bei der Erstellung von Modulen\r\n* Systemunabhängiges sowie plattformübergreifendes Arbeiten über den Webbrowser\r\n* Linkmanagement\r\n* Keine Einschränkungen bei der Entwicklung von barrierefreiem Webdesign\r\n* Aufnahme von Metadaten für Suchmaschinen möglich\r\n* Suchfunktionen können integriert werden\r\n* Rechteverteilung sind möglich\r\n* Medienverwaltung über Medienpool (HTML, XML, PDF, MP3, DOC, JPEG, GIF etc.)\r\n* Import / Export Funktion ermöglicht Projektsicherung\r\n* Einbindung von Erweiterungen/Addons für unterschiedlichste Funktionen, auf der REDAXO-Website gibt es zahlreiche Addons zum Download\r\n* REDAXO passt sich dem eigenen Wissensstand an\r\n* REDAXO basiert auf PHP / MySQL ','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',8,3,1181734163,1237975038,'admin','admin',0,0),
  (53,0,1,54,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','screenshot_kategorie_edit.png,screenshot_content.png,screenshot_content_edit.png,screenshot_medienpool.png,screenshot_benutzerverwaltu.png,screenshot_module.png','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',11,9,1192189262,1237383922,'admin','admin',0,0),
  (16,0,1,0,'Für wen ist REDAXO','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',9,1,1181734367,1237973439,'admin','admin',0,0),
  (17,0,1,16,'h2. Für wen ist REDAXO\r\n\r\nREDAXO ist für alle, die Websites erstellen, und für Nutzer, die mittels einer erstellten REDAXO-Website Inhalte verwalten.\r\n\r\nh2. Für Webdesigner und Administratoren - Erstellung und Gestaltung des Systems\r\n\r\nREDAXO ist kein Plug+Play-System! REDAXO ist für individuelle Lösungen gedacht, daher sind Kenntnisse von HTML und CSS unabdingbar, und Grundkenntnisse in PHP sollten ebenfalls vorhanden sein. REDAXO lässt sich sehr einfach installieren; Anpassungen sind leicht zu realisieren.\r\n\r\nDer größte Vorteil von REDAXO liegt in der Flexibilität. Die Ausgabe von REDAXO ist komplett beeinflussbar, das heißt: Mittels HTML und CSS lassen sich alle denkbaren Designs umsetzen. Ebenso kann man ohne weiteres barrierefreie Websites realisieren.\r\n\r\nh2. Für Redakteure - Verwaltung von Inhalten\r\n\r\nRedakteure brauchen zur Bedienung von REDAXO keine besonderen Kenntnisse. Der Schulungsaufwand ist auch für unerfahrene Nutzer gering. Die Struktur ist klar und übersichtlich aufgebaut, ohne erschlagende Funktionsfülle. Der Administrator kann dem Redakteur die Möglichkeiten und Rechte zur Hand geben, mit denen er alle gewünschten Inhalte und Einstellungen vornehmen kann, ohne Gefahr zu laufen, die Seite zu zerstören.\r\n','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',9,3,1181734432,1237975175,'admin','admin',0,0),
  (18,0,1,0,'Features','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',10,1,1181734555,0,'admin','',0,0),
  (19,0,1,18,'h2. Frei gestaltbar\r\n\r\nMittels HTML/CSS und Templates lassen sich alle denkbaren Designs umsetzen - selbst die Administrationsoberfläche (Backend). Die Ausgabe von REDAXO ist komplett beeinflussbar. \r\n\r\nh2. Suchmaschinenfreundlich\r\n\r\nDurch URL-Rewriting, individuelle Meta-Infos und freie Templategestaltung ist die Optimierung für Suchmaschinen gewährleistet.\r\n\r\nh2. Barrierearm und BITV-konform\r\n\r\nREDAXO erfüllt alle Grundvoraussetzungen, die für eine barrierefreie und BITV-konforme Website notwendig sind. Das Frontend kann der jeweilige Ersteller der Seiten barrierearm gestalten. Das Backend ist ebenfalls barrierearm ausgelegt und kann über Accesskeys per Tastatur bedient werden.\r\n\r\nh2. Mehrsprachigkeit und UTF8\r\n\r\nMit REDAXO können auch mehrsprachige Websites mit exotischen Zeichensätzen angeboten werden. Die Unterstützung von UTF8 erleichtert die Sprachverwaltung - egal, ob englisch, italienisch, französisch, eine Sprache aus dem osteuropäischen oder asiatischen Sprachraum. \r\n\r\nh2. Zukunftssicher für Monitor, PDA, Handy ...\r\n\r\nDa die Ausgabe von REDAXO komplett beeinflussbar ist, kann die Website auch für alternative Geräte maßgeschneidert werden.\r\n\r\nh2. Module und Addons\r\n\r\nErweiterungen können als Module/Addons zum Einsatz kommen. Wie alle guten Content Management Systeme unterstützt auch REDAXO benutzerdefinierte Erweiterungen.\r\n\r\nh2. Benutzerverwaltung\r\n\r\nEs können ausgefeilte Benutzerrechte vergeben werden.\r\n\r\nh2. Modularer Aufbau der Inhalte\r\n\r\nDie Inhalte einer Seite werden modular aus verschiedenen Blöcken zusammengesetzt, die man vergleichsweise leicht selbst programmieren kann - z.B. Überschrift, Text, Bildergalerie, Formular ... Dies ermöglicht eine außergewöhnlich flexible Erstellung des Inhalts.\r\n\r\nh2. Erhalt der Design-Vorgaben\r\n\r\nInhalte und Präsentation werden getrennt voneinander gespeichert. Folglich wird der gesamte Inhalt aller Redakteure in einem Design konsistent ausgegeben.\r\n\r\nh2. Standortunabhängige Pflege der Seiten\r\n\r\nREDAXO funktioniert auf jedem Rechner der mit dem Internet verbunden ist. Seiten können von jedem Ort und zu jeder Zeit über einen Browser bearbeitet werden.\r\n\r\nh2. Textinhalte editieren\r\n\r\nDem Redakteur können zum Editieren der Inhalte verschiedene Möglichkeiten zur Verfügung gestellt werden - von festen Texteingabefeldern über den Textile-Editor bis hin zu Wysiwyg-Editoren wie TinyMCE.\r\n','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',10,3,1181734964,1237975465,'admin','admin',0,0),
  (20,0,1,0,'*redaxo.de* | \"http://www.redaxo.de\":http://www.redaxo.de\r\nAktuelle Informationen zu den aktuellen Versionen, die Basis-Intallation und Updates erhalten Sie auf der offiziellen REDAXO-Website.','REDAXO','','','','','','','l','','','','','','','','','','','','rex.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',16,2,1181735049,1237973876,'admin','admin',0,0),
  (21,0,1,0,'*doku.redaxo.de* | \"http://doku.redaxo.de\":http://doku.redaxo.de\r\nDie Online-Dokumentation von REDAXO\r\nDie Dokumentation ist in acht Teile gegliedert um so den Redakteur, dem Einsteiger und dem Profi die geeigneten Anlaufstellen zu bieten. Danke Dagmar - dag.\r\n','Doku','','','','','','','l','','','','','','','','','','','','doku.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',12,2,1181735096,1237973900,'admin','admin',0,0),
  (22,0,1,0,'*wiki.redaxo.de* | \"http://wiki.redaxo.de\":http://wiki.redaxo.de\r\nIm Wiki stehen Ideen und konkrete Beispiele mit Beschreibungen, ohne Prüfung seitens der Entwickler. Danke Sven - koala.','Wiki','','','','','','','l','','','','','','','','','','','','wiki.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',13,2,1181735145,1237973233,'admin','admin',0,0),
  (23,0,1,0,'*forum.redaxo.de* | \"http://forum.redaxo.de\":http://forum.redaxo.de\r\nEine der ersten Anlaufstellen für Support, Fragen, Tipps und \"Insider-Wissen\" ist das Forum zu REDAXO. Danke Community.','Forum','','','','','','','l','','','','','','','','','','','','forum.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',14,2,1181735243,1237973953,'admin','admin',0,0),
  (24,0,1,26,5,100,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,6,1181735295,0,'admin','',0,0),
  (26,0,1,28,'Links zu REDAXO','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,1,1181735334,1237973268,'admin','admin',0,0),
  (29,0,1,0,'Erste Schritte','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,1,1181735397,0,'admin','',0,0),
  (30,0,1,0,'Nehmen Sie Kontakt auf','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',6,1,1181735435,0,'admin','',0,0),
  (35,0,1,30,'*REDAXO*\r\nc/o Yakamara Media GmbH & Co. KG\r\nAnsprechpartner: Jan Kristinus\r\nWandersmannstraße 68\r\n65205 Wiesbaden\r\nTel.: 0611-504.599.21\r\nTel.: 0611-504.599.30\r\n\"www.redaxo.de\":http://www.redaxo.de\r\n\"www.yakamara.de\":http://www.yakamara.de\r\n\r\n*Programmierung der Demo / HTML Layout*\r\nSandra Hundacker [hundertmorgen] - hundertmorgen\r\n\r\n*REDAXO Agenturen auf redaxo.de*\r\n\"www.redaxo.de\":http://www.redaxo.de/242-0-agenturensupport.html\r\n\r\n*Fotos*\r\n\"www.photocase.com\":http://www.photocase.com\r\n','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',6,3,1189526604,1237976151,'admin','admin',0,0),
  (28,0,1,29,'Auf den folgenden Seiten erfahren Sie mehr über die Installation von REDAXO auf dem Webserver Ihrer Wahl. Überprüfen Sie vor Beginn die Systemvoraussetzungen für Ihr Webpaket und lernen Sie mehr über die ersten Schritte mit einem flexiblen Content-Management-System.','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,3,1181735372,1237973283,'admin','admin',0,0),
  (37,0,1,0,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',5,1,1189526791,0,'admin','',0,0),
  (38,0,1,36,'REDAXO unterscheidet sich von anderen Systemen auf den ersten Blick durch sein sehr schlicht gehaltenes und auf grafische Dekoration verzichtendes Backend. So finden sich auch weniger technikorientierte Anwender schnell in den Funktionen zurecht. Der Administrator kann je nach Bedarf einzelne Funktionen zu- oder abschalten. Dadurch ist REDAXO selbst für Netzauftritte mit wenigen Seiten einsetzbar, ohne durch seine Funktionsfülle den eigentlichen Seiteninhalt zu dominieren. (Quelle: Wikipedia)\r\n\r\n\"zurück\":?article_id=5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',15,3,1189526847,1237975651,'admin','admin',0,0),
  (34,0,1,37,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',5,5,1181737408,0,'admin','',0,0),
  (36,0,1,40,'Was ist das Besondere an REDAXO?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',15,1,1189526739,1237973559,'admin','admin',0,0),
  (40,0,1,0,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',15,1,1189527221,0,'admin','',0,0),
  (41,0,1,0,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',17,1,1189527244,1189527244,'admin','admin',0,0),
  (42,0,1,41,'Was sollte einen dazu bewegen, REDAXO zu nutzen?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',17,1,1189527244,1189527273,'admin','admin',0,0),
  (43,0,1,42,'Zitat aus dem Forum: „Die nette Community und der gute Support. ;-) “\r\n\r\n\"zurück\":?article_id=5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',17,3,1189527244,1189527289,'admin','admin',0,0),
  (44,0,1,0,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',18,1,1189527313,1189527313,'admin','admin',0,0),
  (45,0,1,44,'Wann wird der Einsatz von REDAXO empfohlen?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',18,1,1189527313,1237973644,'admin','admin',0,0),
  (46,0,1,45,'Die Praxis hat gezeigt, dass REDAXO für Webauftritte bis ca. 3000 Seiten ohne Probleme oder Geschwindigkeitseinbußen einsetzbar ist. Je nach Konzept der Website und den Seiteninhalten können es bei optimaler Planung aber auch mehr werden. (Quelle: Wikipedia)\r\n\r\n\"zurück\":?article_id=5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',18,3,1189527313,1237975927,'admin','admin',0,0),
  (47,0,1,0,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',19,1,1189527360,1189527360,'admin','admin',0,0),
  (48,0,1,47,'Wie viele Internetpräsentationen wurden bereits mit REDAXO erstellt?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',19,1,1189527360,1237973660,'admin','admin',0,0),
  (49,0,1,48,'Es gibt ca. 950 gelistete Referenzen auf der REDAXO-Seite (Stand März 2009). Man kann jedoch davon ausgehen, daß die wirkliche Anzahl ein vielfaches davon ist.\r\n\r\n\"zurück\":?article_id=5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',19,3,1189527360,1237976010,'admin','admin',0,0),
  (50,0,1,0,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',20,1,1189527486,1189527486,'admin','admin',0,0),
  (51,0,1,50,'Welche Kenntnisse brauche ich, um mit REDAXO arbeiten zu können?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',20,1,1189527486,1237973713,'admin','admin',0,0),
  (52,0,1,51,'REDAXO basiert auf PHP und Mysql. Kenntnisse in dieser Sprache und im Umgang mit der Datenbank sind zwar zu empfehlen, aber nicht unbedingt erforderlich. Anhand der Demo-Versionen kann man bereits eigene Webseiten erstellen und dabei lernen, das System zu nutzen.\r\n\r\n\"zurück\":?article_id=5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',20,3,1189527486,1237973721,'admin','admin',0,0),
  (54,0,1,0,'Screenshots','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',11,1,1192189506,0,'admin','',0,0),
  (55,0,1,7,'Gregor Harlan\r\n\"www.meyerharlan.de\":http://meyerharlan.de','Gregor Harlan','','','','','','','l','','','','','','','','','','','','gregor.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1273065503,1273067553,'admin','admin',0,0);
/*!40000 ALTER TABLE `rex_article_slice` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_clang`;
CREATE TABLE `rex_clang` (
  `id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `rex_clang` WRITE;
/*!40000 ALTER TABLE `rex_clang` DISABLE KEYS */;
INSERT INTO `rex_clang` VALUES 
  (0,'deutsch',0);
/*!40000 ALTER TABLE `rex_clang` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_file`;
CREATE TABLE `rex_file` (
  `file_id` int(11) NOT NULL auto_increment,
  `re_file_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `attributes` text NOT NULL,
  `filetype` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `originalname` varchar(255) NOT NULL,
  `filesize` varchar(255) NOT NULL,
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL default '0',
  `med_description` text,
  `med_copyright` varchar(255) default NULL,
  PRIMARY KEY  (`file_id`),
  KEY `re_file_id` (`re_file_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_file` WRITE;
/*!40000 ALTER TABLE `rex_file` DISABLE KEYS */;
INSERT INTO `rex_file` VALUES 
  (1,0,1,'','text/css','main.css','main.css',3869,0,0,'main.css',1174923001,1174923001,'admin','admin',0,'Grundraster Layout',''),
  (2,0,1,'','text/css','navigation.css','navigation.css',3970,0,0,'navigation.css',1174923036,1174923036,'admin','admin',0,'Navigationselemente',''),
  (3,0,1,'','text/css','content.css','/Applications/MAMP/tmp/php/phpFvKVpk',4724,0,0,'content.css',1174923063,1192194986,'admin','admin',0,'Inhaltselemente global',''),
  (4,0,1,'','text/css','start.css','start.css',1326,0,0,'start.css',1174923094,1174923094,'admin','admin',0,'besondere Elemente - Startseite',''),
  (5,0,1,'','text/css','default.css','default.css',1512,0,0,'default.css',1174923116,1174923116,'admin','admin',0,'besondere Elemente - Inhaltsseiten',''),
  (6,0,2,'','image/jpeg','start_teaser.jpg','start_teaser.jpg',39416,630,217,'Teaser Startseite',1174923162,1174923162,'admin','admin',0,'Teaserbild Startseite',''),
  (7,0,2,'','image/jpeg','main_teaser.jpg','main_teaser.jpg',41139,630,220,'Teaser Inhaltsseiten',1174923186,1174923186,'admin','admin',0,'Teaserbild Inhaltsseiten',''),
  (8,0,2,'','image/gif','bg_nav.gif','bg_nav.gif',929,208,217,'Hintergrund Navigation vertikal',1174923222,1174923222,'admin','admin',0,'',''),
  (9,0,2,'','image/gif','bg_unav.gif','bg_unav.gif',6494,208,172,'Hintergrund unterhalb Navigation',1174923278,1174923278,'admin','admin',0,'',''),
  (10,0,2,'','image/gif','bg_header.gif','bg_header.gif',3656,630,137,'Hintergrund Header',1174923312,1174923312,'admin','admin',0,'',''),
  (11,0,2,'','image/gif','linie_start_block.gif','linie_start_block.gif',11484,313,245,'Hintergrund content start',1174923339,1174923412,'admin','admin',0,'',''),
  (13,0,2,'','image/gif','linie_main_block.gif','linie_main_block.gif',744,3,211,'Hintergrund content default (Linie)',1174923478,1174923478,'admin','admin',0,'',''),
  (14,0,2,'','image/gif','start_bg_header.gif','/tmp/phpJrzxKl',7846,630,137,'Header Start',1174923513,1174923537,'admin','admin',0,'',''),
  (15,0,2,'','image/gif','redaxo_logo_klein.gif','redaxo_logo_klein.gif',4478,220,80,'Logo (Inhaltsseiten)',1174923597,1174923597,'admin','admin',0,'',''),
  (17,0,2,'','image/gif','button.gif','button.gif',132,20,60,'Rollover Buttons',1174923632,1174923632,'admin','admin',0,'',''),
  (18,0,4,'','image/gif','jan.gif','jan.gif',2730,79,79,'Jan',1176638716,1176638716,'admin','admin',0,'',''),
  (19,0,4,'','image/gif','markus.gif','markus.gif',2798,79,79,'Markus',1176638729,1176638729,'admin','admin',0,'',''),
  (21,0,4,'','image/gif','thomas.gif','thomas.gif',2806,79,79,'Thomas',1176638759,1176638759,'admin','admin',0,'',''),
  (22,0,4,'','image/gif','team-bild.gif','team-bild.gif',680,79,79,'Platzhalter',1176638776,1176638776,'admin','admin',0,'',''),
  (23,0,5,'','image/gif','rex.gif','rex.gif',1804,79,79,'Redaxo',1176744752,1176744752,'admin','admin',0,'',''),
  (24,0,5,'','image/gif','doku.gif','doku.gif',1705,79,79,'Doku',1176744910,1176744910,'admin','admin',0,'',''),
  (25,0,5,'','image/gif','wiki.gif','wiki.gif',1669,79,79,'Wiki',1176745021,1176745021,'admin','admin',0,'',''),
  (26,0,5,'','image/gif','forum.gif','forum.gif',1721,79,79,'Forum',1176745122,1176745122,'admin','admin',0,'',''),
  (27,0,6,'','application/x-javascript','ajs_fx.js','AJS_fx.js',3192,0,0,'',1192187888,1192187888,'admin','admin',0,'',''),
  (28,0,6,'','application/x-javascript','ajs.js','AJS.js',10396,0,0,'',1192187893,1192187893,'admin','admin',0,'',''),
  (29,0,6,'','image/gif','g_close.gif','g_close.gif',541,25,30,'',1192187899,1192187899,'admin','admin',0,'',''),
  (30,0,6,'','application/x-javascript','gb_scripts.js','gb_scripts.js',11908,0,0,'',1192187904,1192187904,'admin','admin',0,'',''),
  (31,0,6,'','text/css','gb_styles.css','gb_styles.css',2574,0,0,'',1192187913,1192187913,'admin','admin',0,'',''),
  (32,0,6,'','image/gif','header_bg.gif','header_bg.gif',1188,223,35,'',1192187921,1192187921,'admin','admin',0,'',''),
  (33,0,6,'','image/gif','indicator.gif','indicator.gif',8238,100,100,'',1192187947,1192187947,'admin','admin',0,'',''),
  (34,0,6,'','text/html','loader_frame.html','loader_frame.html',2084,0,0,'',1192187952,1192187952,'admin','admin',0,'',''),
  (35,0,6,'','image/gif','next.gif','next.gif',528,25,30,'',1192187958,1192187958,'admin','admin',0,'',''),
  (36,0,6,'','image/gif','prev.gif','prev.gif',525,25,30,'',1192187961,1192187961,'admin','admin',0,'',''),
  (37,0,6,'','image/gif','w_close.gif','w_close.gif',74,11,11,'',1192187964,1192187964,'admin','admin',0,'',''),
  (38,0,7,'','image/png','screenshot_benutzerverwaltu.png','screenshot_benutzerverwaltu.png',105370,980,650,'Benutzerverwaltung',1192189157,1237382442,'admin','admin',0,'',''),
  (39,0,7,'','image/png','screenshot_content_edit.png','screenshot_content_edit.png',71997,980,650,'Inhaltsansicht: Einfachen Text editieren',1192189169,1237382479,'admin','admin',0,'',''),
  (40,0,7,'','image/png','screenshot_content.png','screenshot_content.png',86971,980,650,'Inhaltsansicht: Editiermodus',1192189174,1237382467,'admin','admin',0,'',''),
  (41,0,7,'','image/png','screenshot_kategorie_edit.png','screenshot_kategorie_edit.png',80826,980,650,'Kategorieansicht: Kategorienamen editieren',1192189180,1237382144,'admin','admin',0,'',''),
  (42,0,7,'','image/png','screenshot_medienpool.png','screenshot_medienpool.png',116370,980,650,'Medienpool zur Verwaltung von Dateien/Bildern',1192189189,1237382405,'admin','admin',0,'',''),
  (44,0,7,'','image/png','screenshot_module.png','screenshot_module.png',96277,980,650,'Modulansicht',1192189197,1237382426,'admin','admin',0,'',''),
  (48,0,4,'','image/gif','sandra.gif','sandra.gif',1154,79,79,'Sandra',1192282192,1192282192,'admin','admin',0,'',''),
  (49,0,4,'','image/gif','gregor.gif','gregor.gif',2590,79,79,'Gregor',1273067539,1273067539,'admin','admin',0,'','');
/*!40000 ALTER TABLE `rex_file` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_file_category`;
CREATE TABLE `rex_file_category` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `re_id` int(11) NOT NULL default '0',
  `path` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `attributes` text NOT NULL,
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `re_id` (`re_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_file_category` WRITE;
/*!40000 ALTER TABLE `rex_file_category` DISABLE KEYS */;
INSERT INTO `rex_file_category` VALUES 
  (1,'Layout - css',0,'|',1174332637,1174922965,'admin','admin','',0),
  (2,'Layout - images',0,'|',1174922935,1174922954,'admin','admin','',0),
  (3,'Inhalt - images',0,'|',1176638683,1176638683,'admin','admin','',0),
  (4,'Team',3,'|3|',1176638698,1176638698,'admin','admin','',0),
  (5,'Schritte',3,'|3|',1176744700,1189871809,'admin','admin','',0),
  (6,'Bildgalerie',1,'|1|',1192187620,1192187620,'admin','admin','',0),
  (7,'Screenshots',3,'|3|',1192189139,1192189139,'admin','admin','',0);
/*!40000 ALTER TABLE `rex_file_category` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_module`;
CREATE TABLE `rex_module` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL default '0',
  `ausgabe` text NOT NULL,
  `eingabe` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `attributes` text NOT NULL,
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_module` WRITE;
/*!40000 ALTER TABLE `rex_module` DISABLE KEYS */;
INSERT INTO `rex_module` VALUES 
  (1,'01 - Headline',0,'<REX_VALUE[2]>REX_VALUE[1]</REX_VALUE[2]>','&Uuml;berschrift:<br />\r\n<input type=\"text\" size=\"50\" name=\"VALUE[1]\" value=\"REX_VALUE[1]\" />\r\n<select name=\"VALUE[2]\" >\r\n<?php\r\nforeach (array(\"h1\",\"h2\",\"h3\",\"h4\",\"h5\",\"h6\") as $value) {\r\n	echo \'<option value=\"\'.$value.\'\" \';\r\n	\r\n	if ( \"REX_VALUE[2]\"==\"$value\" ) {\r\n		echo \'selected=\"selected\" \';\r\n	}\r\n	echo \'>\'.$value.\'</option>\';\r\n}\r\n?>\r\n</select>','admin','',1181731562,0,'',0),
  (2,'01 - Text und/oder Bild [textile]',0,'<?php\r\n\r\nif(OOAddon::isAvailable(\'textile\'))\r\n{\r\n  echo \'<div class=\"team\">\';\r\n\r\n  //  Ausrichtung des Bildes \r\n  if (\"REX_VALUE[9]\" == \"l\") $float = \"floatLeft\";\r\n  if (\"REX_VALUE[9]\" == \"r\") $float = \"floatRight\";\r\n\r\n  //  Wenn Bild eingefuegt wurde, Code schreiben \r\n  $file = \"\";\r\n  if (\"REX_FILE[1]\" != \"\") $file = \'<div class=\"\'.$float.\'\"><img src=\"\'.$REX[\'HTDOCS_PATH\'].\'files/REX_FILE[1]\" title=\"\'.\"REX_VALUE[2]\".\'\" alt=\"\'.\"REX_VALUE[2]\".\'\" /></div>\';\r\n\r\n  $textile = \'\';\r\n  if(REX_IS_VALUE[1])\r\n  {\r\n    $textile = htmlspecialchars_decode(\"REX_VALUE[1]\");\r\n    $textile = str_replace(\"<br />\",\"\",$textile);\r\n    $textile = rex_a79_textile($textile);\r\n    $textile = str_replace(\"###\",\"&#x20;\",$textile);\r\n  } \r\n  print $file.$textile;\r\n\r\n  echo \'</div>\';\r\n}\r\nelse\r\n{\r\n  echo rex_warning(\'Dieses Modul benötigt das \"textile\" Addon!\');\r\n}\r\n\r\n?>','<?php\r\nif(OOAddon::isAvailable(\'textile\'))\r\n{\r\n?>\r\n\r\n<strong>Fliesstext</strong>:<br />\r\n<textarea name=\"VALUE[1]\" cols=\"80\" rows=\"10\" class=\"inp100\">REX_HTML_VALUE[1]</textarea>\r\n<br /><br />\r\n\r\n<strong>Artikelfoto</strong>:<br />\r\nREX_MEDIA_BUTTON[1]\r\n<?php\r\nif (\"REX_FILE[1]\" != \"\") {\r\n        echo \"<br /><strong>Vorschau</strong>:<br />\";\r\n	echo \"<img src=\".$REX[\'HTDOCS_PATH\'].\"/files/REX_FILE[1]><br />\";\r\n}\r\n?>\r\n\r\n<br />\r\n<strong>Title des Fotos</strong>:<br />\r\n<input type=\"text\" name=\"VALUE[2]\" value=\"REX_VALUE[2]\" size=\"80\" class=\"inp100\" />\r\n<br /><br />\r\n\r\n<strong>Ausrichtung des Artikelfotos</strong>:<br />\r\n<select name=\"VALUE[9]\" class=\"inp100\">\r\n	<option value=\'l\' <?php if (\"REX_VALUE[9]\" == \'l\') echo \'selected\'; ?>>links vom Text</option>\r\n</select><br />\r\n<br />\r\n<br />\r\n\r\n<?php\r\nrex_a79_help_overview(); \r\n\r\n}\r\nelse\r\n{\r\n  echo rex_warning(\'Dieses Modul benötigt das \"textile\" Addon!\');\r\n}\r\n\r\n?>','admin','admin',1181731594,1237372322,'',0),
  (3,'01 - Text [textile]',0,'<?php\r\n\r\nif(OOAddon::isAvailable(\'textile\'))\r\n{\r\n  // Fliesstext \r\n  $textile = \'\';\r\n  if(REX_IS_VALUE[1])\r\n  {\r\n    $textile = htmlspecialchars_decode(\"REX_VALUE[1]\");\r\n    $textile = str_replace(\"<br />\",\"\",$textile);\r\n    $textile = rex_a79_textile($textile);\r\n    $textile = str_replace(\"###\",\"&#x20;\",$textile);\r\n    print \'<div class=\"txt-img\">\'. $textile . \'</div>\';\r\n  } \r\n}\r\nelse\r\n{\r\n  echo rex_warning(\'Dieses Modul benötigt das \"textile\" Addon!\');\r\n}\r\n\r\n?>','<?php\r\n\r\nif(OOAddon::isAvailable(\'textile\'))\r\n{\r\n?>\r\n\r\n<strong>Fliesstext</strong>:<br />\r\n<textarea name=\"VALUE[1]\" cols=\"80\" rows=\"10\" class=\"inp100\">REX_HTML_VALUE[1]</textarea>\r\n<br />\r\n\r\n<?php\r\n\r\nrex_a79_help_overview(); \r\n\r\n}else\r\n{\r\n  echo rex_warning(\'Dieses Modul benötigt das \"textile\" Addon!\');\r\n}\r\n\r\n?>','admin','admin',1181731625,1237372330,'',0),
  (5,'05 - Artikelliste',0,'<?php\r\n\r\n$cat = OOCategory::getCategoryById($this->getValue(\"category_id\"));\r\n$article = $cat->getArticles();\r\n\r\nif (is_array($article)) \r\n{\r\n  foreach ($article as $var) \r\n  {\r\n    $articleId = $var->getId();\r\n    $articleName = $var->getName();\r\n    $articleDescription = $var->getDescription();\r\n    if (!$var->isStartpage()) \r\n    {\r\n      echo \'<a href=\"\'.rex_getUrl($articleId).\'\" class=\"faq\">\'.$articleName.\'</a><br />\';\r\n    }\r\n  }\r\n}\r\n\r\n?>','','admin','admin',1181731691,1237975749,'',0),
  (6,'05 - Kategorienliste',0,'<?php\r\n\r\n$cat = OOCategory :: getCategoryById($this->getValue(\'category_id\'));\r\n$cats = $cat->getChildren();\r\n\r\n$itemsPerSide = \"REX_VALUE[1]\";\r\n$wordsPerArticle = \"REX_VALUE[2]\";\r\n\r\nif (is_array($cats))\r\n{\r\n  $i = 0;\r\n  foreach ($cats as $cat)\r\n  {\r\n    $i += 1;\r\n    if ($i <= $itemsPerSide)\r\n    {\r\n      if ($cat->isOnline())\r\n      {\r\n\r\n        $catId = $cat->getId();\r\n        $catName = $cat->getName();\r\n        $article = $cat->getArticles();\r\n\r\n        if (is_array($article))\r\n        {\r\n          foreach ($article as $var)\r\n          {\r\n            $articleId = $var->getId();\r\n            $articleName = $var->getName();\r\n            $art = new rex_article($articleId);\r\n            $articleContent = $art->getArticle();\r\n\r\n            $articleContent = trim($articleContent);\r\n            $articleContent = str_replace(\'</p>\', \' </p>\', $articleContent);\r\n            $articleContent = str_replace(\'<br />\', \' <br />\', $articleContent);\r\n\r\n            $articlePPath = $REX[\'MEDIAFOLDER\'] . \'files/\' . $var->getValue(\'file\');\r\n\r\n            $output = \'\';\r\n            $words = explode(\' \', $articleContent);\r\n            $wordsCount = count($words);\r\n\r\n            if ($wordsCount < $wordsPerArticle)\r\n              $wEnd = $wordsCount;\r\n            else\r\n              $wEnd = $wordsPerArticle;\r\n\r\n            for ($w = 0; $w < $wEnd; $w++)\r\n            {\r\n              $output .= $words[$w] . \' \';\r\n            }\r\n\r\n            $output = trim($output);\r\n\r\n            $isCloseParagraph = substr($output, -4);\r\n            $isCloseDiv = substr($output, -10);\r\n            $link = \'<a href=\"\' . rex_getUrl($articleId) . \'\" class=\"more\"> ...mehr</a>\';\r\n            $newString = $link . \'</p>\';\r\n\r\n            if ($isCloseParagraph == \'</p>\')\r\n            {\r\n              $output = substr_replace($output, $newString, -4);\r\n            }\r\n            elseif ($isCloseDiv == \'</p></div>\')\r\n            {\r\n              $output = substr_replace($output, $newString.\'</div>\', -10);\r\n            }\r\n            else\r\n            {\r\n              $output .= $newString;\r\n            }\r\n\r\n            // print \'<h2>\'.$articleName.\'</h2>\';\r\n            print \'<div class=\"txt-img\">\' . $output . \'</div>\';\r\n\r\n          }\r\n        }\r\n      }\r\n    }\r\n  }\r\n}\r\n?>','<?php\r\n\r\n//---MODULE BY----------------------\r\n//-- Wegener IT\r\n//-- Mattias Beckmann\r\n//-- www.wegener-it.de\r\n//----------------------------------\r\n\r\n?>\r\n\r\n<strong>Anzahl der Artikel pro Seite</strong><br />\r\n<input name=\"VALUE[1]\" value=\"REX_VALUE[1]\" class=\"inp100\" />\r\n\r\n<br /><br />\r\n<strong>Anzahl der Wörter pro Artikel</strong><br />\r\n<input name=\"VALUE[2]\" value=\"REX_VALUE[2]\" class=\"inp100\" />\r\n\r\n<br />','admin','admin',1181731741,1237972859,'',0),
  (8,'04 - Artikelweiterleitung',0,'<?php\r\n\r\nif($REX[\'REDAXO\']!=1 && REX_ARTICLE_ID != REX_LINK_ID[1])\r\n{\r\n  if ( REX_LINK_ID[1] != 0) \r\n  {\r\n   rex_redirect(REX_LINK_ID[1], $REX[\'CUR_CLANG\']);\r\n  }\r\n}else\r\n{\r\n  echo \"Weiterleitung zu <a href=\'index.php?page=content&article_id=REX_LINK_ID[1]&mode=edit\'>Artikel           REX_LINK[1]</a>\";\r\n}\r\n\r\n?>','Artikel, zu dem Weitergeleitet werden soll:<br /><br />\r\nREX_LINK_BUTTON[1]','admin','admin',1181731807,1237372365,'',0),
  (9,'03 - Bildgalerie',0,'<?php\r\n\r\nif (!isset($REX[\'MODULE_BILDGALERIE_ID\'])) $REX[\'MODULE_BILDGALERIE_ID\'] = 0;\r\nelse $REX[\'MODULE_BILDGALERIE_ID\']++;\r\n\r\nif ($REX[\'MODULE_BILDGALERIE_ID\']==0)\r\n{\r\n?>\r\n<script type=\"text/javascript\">\r\nvar GB_ROOT_DIR = \"files/\";\r\n</script>\r\n<script type=\"text/javascript\" src=\"files/ajs.js\"></script>\r\n<script type=\"text/javascript\" src=\"files/ajs_fx.js\"></script>\r\n<script type=\"text/javascript\" src=\"files/gb_scripts.js\"></script>\r\n<link href=\"files/gb_styles.css\" rel=\"stylesheet\" type=\"text/css\" />\r\n\r\n<?php\r\n}\r\n?>\r\n\r\n<div class=\"galerie\">\r\n\r\n<?php\r\n\r\n$pics_string = \"REX_MEDIALIST[1]\";\r\nif($pics_string != \'\')\r\n{\r\n  $i = 1;\r\n  $pics = explode(\',\',$pics_string);\r\n\r\n  foreach($pics as $pic)\r\n  {\r\n    echo \'<div class=\"image\">\';\r\n\r\n    $title = \'\';\r\n    if ($file = OOMedia::getMediaByFileName($pic)) $title = $file->getTitle();\r\n\r\n    echo \'<a href=\"\'.$REX[\'HTDOCS_PATH\'].\'/files/\'.$pic.\'\" rel=\"gb_imageset[galerie\'.$REX[\'MODULE_BILDGALERIE_ID\'].\']\"><img src=\"\'.$REX[\'HTDOCS_PATH\'].\'index.php?rex_img_type=gallery_overview&rex_img_file=\'.$pic.\'\" title=\"\'.$title.\'\" alt=\"\'.$title.\'\" /></a>\';\r\n\r\n    echo \'<p>\'.$title.\'</p>\';\r\n    echo \'</div>\';\r\n\r\n    if($i % 2 == 0)\r\n      echo \'<div class=\"clearer\"></div>\';\r\n\r\n    $i++;  \r\n  }\r\n}\r\n\r\n?></div>','Bitte Bilder auswählen:\r\n<br />REX_MEDIALIST_BUTTON[1]\r\n<br /><br />','admin','admin',1192188185,1267641208,'',0);
/*!40000 ALTER TABLE `rex_module` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_module_action`;
CREATE TABLE `rex_module_action` (
  `id` int(11) NOT NULL auto_increment,
  `module_id` int(11) NOT NULL default '0',
  `action_id` int(11) NOT NULL default '0',
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
DROP TABLE IF EXISTS `rex_template`;
CREATE TABLE `rex_template` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `active` tinyint(1) NOT NULL default '0',
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL default '0',
  `updatedate` int(11) NOT NULL default '0',
  `attributes` text NOT NULL,
  `revision` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

LOCK TABLES `rex_template` WRITE;
/*!40000 ALTER TABLE `rex_template` DISABLE KEYS */;
INSERT INTO `rex_template` VALUES 
  (1,'','default','<?php\r\n\r\n\r\n// ------ DESCRIPTION/KEYWORDS\r\n$OOStartArticle = OOArticle::getArticleById($REX[\'START_ARTICLE_ID\'], $REX[\'CUR_CLANG\']);\r\n$meta_beschreibung = $OOStartArticle->getValue(\"art_description\");\r\n$meta_suchbegriffe = $OOStartArticle->getValue(\"art_keywords\");\r\n\r\nif($this->getValue(\"art_description\") != \"\")\r\n	$meta_beschreibung = $this->getValue(\"art_description\");\r\n	\r\nif($this->getValue(\"art_keywords\") != \"\")\r\n	$meta_suchbegriffe = $this->getValue(\"art_keywords\");\r\n\r\n\r\n?><!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\r\n	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"de\" lang=\"de\">\r\n<head>\r\n	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\r\n	<title><?php print $REX[\'SERVERNAME\'].\' | \'.$this->getValue(\"name\"); ?></title>\r\n	<meta name=\"keywords\" content=\"<?php print htmlspecialchars($meta_suchbegriffe); ?>\" />\r\n	<meta name=\"description\" content=\"<?php print htmlspecialchars($meta_beschreibung); ?>\" />\r\n\r\n	<link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo $REX[\'HTDOCS_PATH\'] ?>files/main.css\" media=\"screen\" />\r\n	<link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo $REX[\'HTDOCS_PATH\'] ?>files/navigation.css\" media=\"screen\" />\r\n	<link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo $REX[\'HTDOCS_PATH\'] ?>files/content.css\" media=\"screen\" />\r\n	<link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo $REX[\'HTDOCS_PATH\'] ?>files/default.css\" media=\"screen\" />\r\n\r\n\r\n</head>\r\n\r\n<body class=\"mainPage\">\r\n	<div>\r\n		<a name=\"top\" id=\"top\"></a>\r\n	</div>\r\n\r\n	<div id=\"site-content\">\r\n		<div id=\"column\">\r\n			\r\n			<div id=\"header\">\r\n				<div id=\"logo\">\r\n					<a href=\"<?php echo $REX[\'HTDOCS_PATH\'] ?>index.php\" title=\"Zur&uuml;ck zur Startseite\">REDAXO Demo</a>\r\n				</div>\r\n			</div>\r\n			\r\n			<div id=\"content\">\r\n\r\n				<div id=\"main-content\">\r\n\r\n					<div id=\"nav\">\r\n						REX_TEMPLATE[2]\r\n						<p class=\"copy\">&copy; by <a href=\"http://www.redaxo.de\">REDAXO</a></p>\r\n					</div>\r\n\r\n					<div id=\"main\">\r\n						<div id=\"main-block\">\r\n							<div id=\"main-teaser\">\r\n								Slogan: Einfach, flexibel, sinnvoll\r\n							</div>\r\n\r\n							<div id=\"main-content-block\">\r\n								REX_TEMPLATE[3]\r\n								REX_ARTICLE[]\r\n							</div>\r\n						</div>\r\n					</div>\r\n					<br class=\"clear\" />\r\n\r\n				</div>\r\n\r\n			</div>\r\n\r\n			<div id=\"footer\">\r\n				<p class=\"floatRight\"><a href=\"http://www.redaxo.de\">REDAXO CMS</a> - SIMPLE DEMO | XHTML 1.0 Strict | pictures by <a href=\"http://www.photocase.com\">photocase.com</a></p>\r\n				<br class=\"clear\" />\r\n			</div>\r\n\r\n		</div>\r\n	</div>\r\n<div style=\"display:none;\">Eigene Templates sind besser - REDAXO</div>\r\n</body>\r\n</html>\r\n',1,'admin','admin',1239286105,1239286105,'a:2:{s:7:\"modules\";a:1:{i:1;a:1:{s:3:\"all\";s:1:\"1\";}}s:5:\"ctype\";a:0:{}}',0),
  (3,'','Navigation: Breadcrumb','<?php\r\n\r\n// ---------- BREADCRUMB\r\n\r\n// Beginne in der Wurzelkategorie\r\n// 1 Ebene Tief\r\n// Nicht aufklappen (hier egal da nur 1 Ebene)\r\n// Offline ausblenden \r\n\r\n$category_id = 0;\r\n$includeCurrent = TRUE;\r\n\r\n// navigation generator erstellen\r\n$nav = rex_navigation::factory();\r\n\r\necho \'<div id=\"breadcrumb\">\';\r\nif ($REX[\'CUR_CLANG\'] == 1)\r\n{\r\n  echo \'<p>You are here:</p>\'. $nav->getBreadcrumb(\'Startpage\', $includeCurrent, $category_id);\r\n}\r\nelse\r\n{\r\n  echo \'<p>Sie befinden sich hier:</p>\'. $nav->getBreadcrumb(\'Startseite\', $includeCurrent, $category_id);\r\n}\r\necho \'</div>\';\r\n?>',0,'admin','admin',1237380161,1237380161,'a:2:{s:7:\"modules\";a:1:{i:1;a:1:{s:3:\"all\";s:1:\"1\";}}s:5:\"ctype\";a:0:{}}',0),
  (2,'','Navigation: Links','<?php\r\n\r\n// navigation generator erstellen\r\n$nav = rex_navigation::factory();\r\n\r\n// ---------- HEAD NAVI\r\n\r\n// Beginne in der Wurzelkategorie\r\n// 1 Ebene Tief\r\n// Nicht aufklappen (hier egal da nur 1 Ebene)\r\n// Offline ausblenden\r\n\r\n$category_id = 0;\r\n$depth = 3;\r\n$open = FALSE;\r\n$ignore_offlines = TRUE;\r\n\r\necho $nav->get($category_id, $depth, $open, $ignore_offlines);\r\n\r\n?>',0,'admin','admin',1237373552,1237373552,'a:2:{s:7:\"modules\";a:1:{i:1;a:1:{s:3:\"all\";s:1:\"1\";}}s:5:\"ctype\";a:0:{}}',0);
/*!40000 ALTER TABLE `rex_template` ENABLE KEYS */;
UNLOCK TABLES;

