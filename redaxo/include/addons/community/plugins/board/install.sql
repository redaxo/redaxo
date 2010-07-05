DROP TABLE IF EXISTS `rex_com_board`;

CREATE TABLE `rex_com_board` (
  `message_id` int(11) NOT NULL auto_increment,
  `re_message_id` int(11) NOT NULL default '0',
  `last_message_id` int(11) NOT NULL default '0',
  `board_id` varchar(255) NOT NULL default '',
  `user_id` varchar(255) NOT NULL default '',
  `user_email` varchar(255) NOT NULL default '',
  `user_registered` tinyint(1) NOT NULL default '0',
  `replies` int(11) NOT NULL default '0',
  `last_entry` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `stamp` int(11) NOT NULL default '0',
  `status` int(1) NOT NULL default '0',
  `file_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`message_id`)
);