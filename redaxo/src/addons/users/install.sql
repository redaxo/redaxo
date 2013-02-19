CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%user_role` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `description` text,
    `perms` text NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` int(11) NOT NULL,
    `updatedate` int(11) NOT NULL,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
