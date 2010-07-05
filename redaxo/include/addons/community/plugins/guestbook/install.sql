DROP TABLE IF EXISTS `rex_com_guestbook`;

CREATE TABLE `rex_com_guestbook` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `from_user_id` int(11) NOT NULL default '0',
  `text` longtext NOT NULL,
  `create_datetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);