CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%user_role` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `description` text,
    `perms` text NOT NULL,
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    `revision` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
