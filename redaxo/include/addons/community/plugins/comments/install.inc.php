<?php

$ins = new rex_sql();
$ins->setQuery("CREATE TABLE IF NOT EXISTS `rex_com_comment` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `type` varchar(255) NOT NULL default '',
  `type_id` int(11) NOT NULL default '0',
  `comment` text NOT NULL,
  `status` tinyint(4) NOT NULL default '0',
  `create_datetime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
)");

$REX['ADDON']['install']['comments'] = 1;

?>