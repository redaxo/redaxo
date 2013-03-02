## Redaxo Database Dump Version 5
## Prefix rex_

CREATE TABLE `rex_clang` (
    `id` int(11) NOT NULL,
    `code` varchar(255) NOT NULL,
    `name` varchar(255) NOT NULL,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `rex_clang` VALUES ('0', 'de', 'deutsch', 0);

CREATE TABLE `rex_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `namespace` varchar(75) NOT NULL,
    `key` varchar(255) NOT NULL,
    `value` text NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_key` (`namespace`,`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE `rex_user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `description` text,
    `login` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `status` tinyint(1) NOT NULL,
    `admin` tinyint(1) NOT NULL,
    `language` varchar(255) NOT NULL,
    `startpage` varchar(255) NOT NULL,
    `role` int(11) NOT NULL,
    `login_tries` tinyint(4) DEFAULT '0',
    `createuser` varchar(255) NOT NULL,
    `updateuser` varchar(255) NOT NULL,
    `createdate` int(11) NOT NULL,
    `updatedate` int(11) NOT NULL,
    `lasttrydate` int(11) DEFAULT '0',
    `session_id` varchar(255) DEFAULT NULL,
    `cookiekey` varchar(255) DEFAULT NULL,
    `revision` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
