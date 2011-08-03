## Redaxo Database Dump Version 5
## Prefix rex_

CREATE TABLE `rex_clang` ( `id` int(11) NOT NULL  , `name` varchar(255) NOT NULL  , `revision` int(11) NOT NULL  , PRIMARY KEY (`id`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE `rex_user` ( `user_id` int(11) NOT NULL auto_increment, `name` varchar(255) , `description` text , `login` varchar(50) NOT NULL  , `psw` varchar(50) NOT NULL  , `status` varchar(5) NOT NULL  ,  `role` int(11) NOT NULL  , `rights` text NOT NULL  , `login_tries` tinyint(4) DEFAULT 0 , `createuser` varchar(255) NOT NULL  , `updateuser` varchar(255) NOT NULL  , `createdate` int(11) NOT NULL , `updatedate` int(11) NOT NULL , `lasttrydate` int(11) DEFAULT 0 , `session_id` varchar(255) , `cookiekey` varchar(255) , `revision` int(11) NOT NULL, PRIMARY KEY(`user_id`))ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE `rex_user_role` ( `id` int(11) NOT NULL auto_increment, `name` varchar(255) , `description` text ,  `rights` text NOT NULL  , `createuser` varchar(255) NOT NULL  , `updateuser` varchar(255) NOT NULL  , `createdate` int(11) NOT NULL , `updatedate` int(11) NOT NULL , `revision` int(11) NOT NULL, PRIMARY KEY(`id`))ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE `rex_config` ( `cid` int(11) NOT NULL AUTO_INCREMENT, `namespace` varchar(75) NOT NULL, `key` varchar(255) NOT NULL, `value` text NOT NULL, PRIMARY KEY (`cid`), UNIQUE KEY `unique_key` (`namespace`,`key`)) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `rex_clang` VALUES ('0','deutsch', 0);

ALTER TABLE rex_user ADD UNIQUE INDEX `login` (`login`(50)); 