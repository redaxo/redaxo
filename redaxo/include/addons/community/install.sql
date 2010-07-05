
DROP TABLE IF EXISTS `rex_com_user`;

CREATE TABLE `rex_com_user` (
  `id` int(11) NOT NULL auto_increment,
  `login` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `status` int(11) NOT NULL default '0',
  `admin` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `activation_key` varchar(255) NOT NULL default '',
  `gender` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);

DROP TABLE IF EXISTS `rex_com_user_field`;

CREATE TABLE `rex_com_user_field` (
  `id` int(11) NOT NULL auto_increment,
  `prior` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `userfield` varchar(255) NOT NULL default '',
  `type` int(11) NOT NULL default '0',
  `extra1` varchar(255) NOT NULL default '',
  `extra2` varchar(255) NOT NULL default '',
  `extra3` varchar(255) NOT NULL default '',
  `inlist` tinyint(4) NOT NULL default '0',
  `editable` tinyint(4) NOT NULL default '0',
  `mandatory` tinyint(4) NOT NULL default '0',
  `defaultvalue` varchar(255) NOT NULL default '',
  `unique` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);

INSERT INTO `rex_com_user_field` VALUES (1, 10, 'Login', 'login', 2, '', '', '', 1, 1, 1, '', 1);
INSERT INTO `rex_com_user_field` VALUES (2, 20, 'Passwort', 'password', 2, '', '', '', 0, 1, 0, '', 0);
INSERT INTO `rex_com_user_field` VALUES (3, 30, 'Email', 'email', 2, '', '', '', 1, 1, 1, '', 1);
INSERT INTO `rex_com_user_field` VALUES (4, 40, 'Status', 'status', 5, 'inaktiv=0;aktiv=1', '', '', 1, 1, 1, '0', 0);
INSERT INTO `rex_com_user_field` VALUES (5, 50, 'Admin', 'admin', 6, '', '', '', 1, 1, 0, '0', 0);
INSERT INTO `rex_com_user_field` VALUES (6, 60, 'Nachname', 'name', 2, '', '', '', 1, 1, 0, '', 0);
INSERT INTO `rex_com_user_field` VALUES (7, 61, 'Vorname', 'firstname', 2, '', '', '', 1, 1, 0, '', 0);
INSERT INTO `rex_com_user_field` VALUES (8, 101, 'Aktivierungsschluessel', 'activation_key', 2, '', '', '', 1, 1, 0, '', 0);
INSERT INTO `rex_com_user_field` VALUES (9, 102, 'Geschlecht', 'gender', 5, 'Keine Angabe=0;Herr=1;Frau=2', '', '', 1, 1, 0, '', 0);