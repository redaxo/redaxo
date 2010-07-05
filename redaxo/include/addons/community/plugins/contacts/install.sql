DROP TABLE IF EXISTS `rex_com_contact`;

CREATE TABLE `rex_com_contact` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `to_user_id` int(11) NOT NULL default '0',
  `requested` tinyint(4) NOT NULL default '0',
  `accepted` tinyint(4) NOT NULL default '0',
  `create_datetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);