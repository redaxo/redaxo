CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%metainfo_type` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `label` varchar(255) default NULL,
    `dbtype` varchar(255) NOT NULL,
    `dblength` int(11) NOT NULL,
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%metainfo_field` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `title` varchar(255) default NULL,
    `name` varchar(255) default NULL,
    `priority` int(10) unsigned NOT NULL,
    `attributes` text NOT NULL,
    `type_id` int(10) unsigned default NULL,
    `default` varchar(255) NOT NULL,
    `params` text default NULL,
    `validate` text NULL,
    `callback` text NULL,
    `restrictions` text NULL,
    `templates` text NULL,
    `createuser` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `updatedate` datetime NOT NULL,
    PRIMARY KEY  (`id`),
    UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

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
    (6,  'REX_MEDIA_WIDGET', 'varchar', 255),
    (7,  'REX_MEDIALIST_WIDGET', 'text', 0),
    (8,  'REX_LINK_WIDGET', 'varchar', 255),
    (9,  'REX_LINKLIST_WIDGET', 'text', 0)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `dbtype` = VALUES(`dbtype`), `dblength` = VALUES(`dblength`);
