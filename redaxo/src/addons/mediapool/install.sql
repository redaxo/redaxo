CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%media` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `category_id` int(11) NOT NULL,
    `attributes` text,
    `filetype` varchar(255) DEFAULT NULL,
    `filename` varchar(255) DEFAULT NULL,
    `originalname` varchar(255) DEFAULT NULL,
    `filesize` varchar(255) DEFAULT NULL,
    `width` int(11) DEFAULT NULL,
    `height` int(11) DEFAULT NULL,
    `title` varchar(255) DEFAULT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%media_category` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `parent_id` int(11) NOT NULL,
    `path` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `attributes` text,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
