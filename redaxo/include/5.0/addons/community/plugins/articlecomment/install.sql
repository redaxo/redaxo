DROP TABLE IF EXISTS `rex_com_comment`;

CREATE TABLE `rex_com_comment` (
`id` INT NOT NULL AUTO_INCREMENT ,
`user_id` INT NOT NULL ,
`article_id` INT NOT NULL ,
`comment` TEXT NOT NULL ,
`status` tinyint(4) NOT NULL default '0',
`create_datetime` INT NOT NULL ,
PRIMARY KEY ( `id` )
);