CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%metainfo_field` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `title` varchar(255) default NULL,
    `name` varchar(255) default NULL,
    `priority` int(10) unsigned NOT NULL,
    `attributes` text NOT NULL,
    `type` int(10) unsigned default NULL,
    `default` varchar(255) NOT NULL,
    `params` text default NULL,
    `validate` text NULL,
    `callback` text NULL,
    `restrictions` text NULL,
    `createuser` varchar(255) NOT NULL,
    `createdate` int(11) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `updatedate` int(11) NOT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%metainfo_type` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `label` varchar(255) default NULL,
    `dbtype` varchar(255) NOT NULL,
    `dblength` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

INSERT INTO %TABLE_PREFIX%metainfo_type VALUES
    (1,  'text', 'text', 0),
    (2,  'textarea', 'text', 0),
    (3,  'select', 'varchar', 255),
    (4,  'radio', 'varchar', 255),
    (5,  'checkbox', 'varchar', 255),
    (10, 'date', 'text', 0),
    (13, 'time', 'text', 0),
    (11, 'datetime', 'text', 0),
    (12, 'legend', 'text', 0),
    (6,  'REX_MEDIA_BUTTON', 'varchar', 255),
    (7,  'REX_MEDIALIST_BUTTON', 'text', 0),
    (8,  'REX_LINK_BUTTON', 'varchar', 255),
    (9,  'REX_LINKLIST_BUTTON', 'text', 0)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `dbtype` = VALUES(`dbtype`), `dblength` = VALUES(`dblength`);

INSERT INTO `%TABLE_PREFIX%metainfo_field` (`title`, `name`, `priority`, `attributes`, `type`, `default`, `params`, `validate`, `restrictions`, `createuser`, `createdate`, `updateuser`, `updatedate`) VALUES
    ('translate:pool_file_description','med_description','1','','2','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:pool_file_copyright','med_copyright','2','','1','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:online_from','art_online_from','1','','10','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:online_to','art_online_to','2','','10','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:description','art_description','3','','2','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:keywords','art_keywords','4','','2','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:metadata_image','art_file','5','','6','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:teaser','art_teaser','6','','5','','','','','%USER%','%TIME%','%USER%','%TIME%'),
    ('translate:header_article_type','art_type_id','7','size=1','3','','Standard|Zugriff fuer alle','','','%USER%','%TIME%','%USER%','%TIME%')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `priority` = VALUES(`priority`), `attributes` = VALUES(`attributes`), `type` = VALUES(`type`), `default` = VALUES(`default`), `params` = VALUES(`params`), `validate` = VALUES(`validate`), `restrictions` = VALUES(`restrictions`);
