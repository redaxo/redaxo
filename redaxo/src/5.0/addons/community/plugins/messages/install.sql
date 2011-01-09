
DROP TABLE IF EXISTS `rex_com_message`;

CREATE TABLE `rex_com_message` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `from_user_id` int(11) NOT NULL default '0',
  `to_user_id` int(11) NOT NULL default '0',
  `subject` varchar(255) NOT NULL default '',
  `body` mediumtext NOT NULL,
  `create_datetime` int(11) NOT NULL default '0',
  `deleted` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);