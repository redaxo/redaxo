
DROP TABLE IF EXISTS `rex_com_twitter_account`;

CREATE TABLE `rex_com_twitter_account` (
  `id` int(11) NOT NULL auto_increment,
  `status` tinyint(4) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `since_id` varchar(255) NOT NULL,
  `last_update` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
);


DROP TABLE IF EXISTS `rex_com_twitter_entry`;

CREATE TABLE `rex_com_twitter_entry` (
  `id` int(11) NOT NULL auto_increment,
  `twitter_id` varchar(255) NOT NULL,
  `account` varchar(255) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `text` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `created_at` int(11) NOT NULL,
  `truncated` tinyint(4) NOT NULL,
  `added_at` int(11) NOT NULL,
  `location_lng` decimal(18,12) NOT NULL,
  `location_lat` decimal(18,12) NOT NULL,
  `favorited` tinyint(4) NOT NULL,
  `in_reply_to_status_id` bigint(20) NOT NULL,
  `in_reply_to_user_id` bigint(20) NOT NULL,
  PRIMARY KEY  (`id`)
);