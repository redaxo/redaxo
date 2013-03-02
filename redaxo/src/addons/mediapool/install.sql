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
    `createdate` int(11) NOT NULL,
    `updatedate` int(11) NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%media_category` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `re_id` int(11) NOT NULL,
    `path` varchar(255) NOT NULL,
    `createdate` int(11) NOT NULL,
    `updatedate` int(11) NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `attributes` text,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `re_id` (`re_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
