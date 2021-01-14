## Redaxo Database Dump Version 5
## Prefix rex_
## charset utf8mb4

INSERT IGNORE INTO `rex_article` VALUES
(1,1,0,'test category','test category',1,1,1,'|',1,1,1,'2021-01-01 11:37:20','myusername','2021-01-01 11:37:20','myusername',0),
(2,2,0,'test article','',0,0,1,'|',0,1,1,'2021-01-01 11:37:20','myusername','2021-01-01 11:37:20','myusername',0);

INSERT IGNORE INTO `rex_article_slice` VALUES
(1,1,1,1,1,0,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2021-01-01 11:37:20','myusername','2021-01-01 11:37:20','myusername'),
(2,2,1,1,1,0,1,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2021-01-01 11:37:20','myusername','2021-01-01 11:37:20','myusername');

INSERT IGNORE INTO `rex_module` VALUES
(1,'testmodule1','Test Module 1','output','input','2021-01-01 11:37:20','myusername','0000-00-00 00:00:00','',NULL,0);

# update existing default template
REPLACE INTO `rex_template` VALUES
(1,NULL,'Default','REX_ARTICLE[]',1,'2021-01-01 11:37:20','myusername','2021-01-01 11:37:20','myusername','{\"ctype\":{\"1\":\"ctype1\",\"2\":\"ctype2\"},\"modules\":{\"1\":{\"all\":\"1\"},\"2\":{\"all\":\"1\"}},\"categories\":{\"all\":\"1\"}}',0);
