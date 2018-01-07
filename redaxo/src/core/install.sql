## Redaxo Database Dump Version 5
## Prefix rex_

CREATE TABLE `rex_clang` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `priority` int(10) unsigned NOT NULL,
    `status` tinyint(1) NOT NULL,
    `revision` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `rex_clang` VALUES (1, 'de', 'deutsch', 1, 1, 0);

CREATE TABLE `rex_config` (
    `namespace` varchar(75) NOT NULL,
    `key` varchar(255) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`namespace`, `key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `rex_user` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `description` text,
    `login` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `email` varchar(255),
    `status` tinyint(1) NOT NULL,
    `admin` tinyint(1) NOT NULL,
    `language` varchar(255) NOT NULL,
    `startpage` varchar(255) NOT NULL,
    `role` text,
    `login_tries` tinyint(4) DEFAULT '0',
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` datetime NOT NULL,
    `updatedate` datetime NOT NULL,
    `lasttrydate` datetime NOT NULL,
    `lastlogin` datetime,
    `session_id` varchar(255) DEFAULT NULL,
    `cookiekey` varchar(255) DEFAULT NULL,
    `revision` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
