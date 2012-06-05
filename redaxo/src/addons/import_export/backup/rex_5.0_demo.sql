## Redaxo Database Dump Version 5
## Prefix rex_
## charset utf-8

DROP TABLE IF EXISTS `rex_action`;
CREATE TABLE `rex_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `preview` text,
  `presave` text,
  `postsave` text,
  `previewmode` tinyint(4) DEFAULT NULL,
  `presavemode` tinyint(4) DEFAULT NULL,
  `postsavemode` tinyint(4) DEFAULT NULL,
  `createuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `rex_article`;
CREATE TABLE `rex_article` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `re_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `catname` varchar(255) NOT NULL,
  `catprior` int(11) NOT NULL,
  `attributes` text NOT NULL,
  `startpage` tinyint(1) NOT NULL,
  `prior` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `clang` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL,
  `art_online_from` text,
  `art_online_to` text,
  `art_description` text,
  `art_keywords` text,
  `art_file` varchar(255) DEFAULT '',
  `art_teaser` varchar(255) DEFAULT '',
  `art_type_id` varchar(255) DEFAULT '',
  PRIMARY KEY (`pid`),
  UNIQUE KEY `find_articles` (`id`,`clang`),
  KEY `id` (`id`),
  KEY `clang` (`clang`),
  KEY `re_id` (`re_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_article` WRITE;
/*!40000 ALTER TABLE `rex_article` DISABLE KEYS */;
INSERT INTO `rex_article` VALUES
  (1,1,0,'Home','Home',1,'',1,1,'|',1,1324155018,1324157581,1,0,'admin','admin',0,'','','','','','',''),
  (2,2,0,'Team','Team',2,'',1,1,'|',1,1324155667,1324159117,1,0,'admin','admin',0,'','','','','','',''),
  (3,3,0,'System','System',3,'',1,1,'|',1,1324155667,1324159263,1,0,'admin','admin',0,'','','','','','',''),
  (4,4,0,'Erste Schritte','Erste Schritte',4,'',1,1,'|',1,1324155672,1324196607,1,0,'admin','admin',0,'','','','','','',''),
  (5,5,0,'FAQ','FAQ',5,'',1,1,'|',1,1324155671,1324200495,1,0,'admin','admin',0,'','','','','','',''),
  (6,6,0,'Kontakt / Impressum','Kontakt / Impressum',6,'',1,1,'|',1,1324155670,1324196556,1,0,'admin','admin',0,'','','','','','',''),
  (7,7,3,'Was ist REDAXO','Was ist REDAXO',1,'',1,1,'|3|',1,1324159251,1324159209,1,0,'admin','admin',0,'','','','','','',''),
  (8,8,3,'Für wen ist REDAXO','Für wen ist REDAXO',2,'',1,1,'|3|',1,1324159251,1324159245,1,0,'admin','admin',0,'','','','','','',''),
  (9,9,3,'Features','Features',3,'',1,1,'|3|',1,1324159434,1324159451,1,0,'admin','admin',0,'','','','','','',''),
  (10,10,3,'Screenshots','Screenshots',4,'',1,1,'|3|',1,1324159494,1324215807,1,0,'admin','admin',0,'','','','','','',''),
  (11,11,4,'REDAXO','REDAXO',1,'',1,1,'|4|',1,1324195747,1324195811,1,0,'admin','admin',0,'','','','','','',''),
  (12,12,4,'Doku','Doku',2,'',1,1,'|4|',1,1324195748,1324195852,1,0,'admin','admin',0,'','','','','','',''),
  (13,13,4,'Wiki','Wiki',3,'',1,1,'|4|',1,1324195748,1324195886,1,0,'admin','admin',0,'','','','','','',''),
  (14,14,4,'Forum','Forum',4,'',1,1,'|4|',1,1324195752,1324196023,1,0,'admin','admin',0,'','','','','','',''),
  (15,15,4,'GitHub','GitHub',5,'',1,1,'|4|',0,1324200150,1324195743,1,0,'admin','admin',0,'','','','','','',''),
  (16,16,5,'Was ist das Besondere an REDAXO?','FAQ',0,'',0,2,'|5|',1,1324197289,1324197441,1,0,'admin','admin',0,'','','','','','',''),
  (18,17,5,'Was sollte einen dazu bewegen, REDAXO zu nutzen?','FAQ',0,'',0,3,'|5|',1,1324197543,1324197622,1,0,'admin','admin',0,'','','','','','',''),
  (19,18,5,'Wann wird der Einsatz von REDAXO empfohlen?','FAQ',0,'',0,4,'|5|',1,1324197621,1324200181,1,0,'admin','admin',0,'','','','','','',''),
  (20,19,5,'Wie viele Internetpräsentationen wurden bereits mit REDAXO erstellt?','FAQ',0,'',0,5,'|5|',1,1324198070,1324200436,1,0,'admin','admin',0,'','','','','','',''),
  (21,20,5,'Welche Kenntnisse brauche ich, um mit REDAXO arbeiten zu können?','FAQ',0,'',0,6,'|5|',1,1324198089,1324200333,1,0,'admin','admin',0,'','','','','','','');
/*!40000 ALTER TABLE `rex_article` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_article_slice`;
CREATE TABLE `rex_article_slice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clang` int(11) NOT NULL,
  `ctype` int(11) NOT NULL,
  `prior` int(11) NOT NULL,
  `value1` text,
  `value2` text,
  `value3` text,
  `value4` text,
  `value5` text,
  `value6` text,
  `value7` text,
  `value8` text,
  `value9` text,
  `value10` text,
  `value11` text,
  `value12` text,
  `value13` text,
  `value14` text,
  `value15` text,
  `value16` text,
  `value17` text,
  `value18` text,
  `value19` text,
  `value20` text,
  `file1` varchar(255) DEFAULT NULL,
  `file2` varchar(255) DEFAULT NULL,
  `file3` varchar(255) DEFAULT NULL,
  `file4` varchar(255) DEFAULT NULL,
  `file5` varchar(255) DEFAULT NULL,
  `file6` varchar(255) DEFAULT NULL,
  `file7` varchar(255) DEFAULT NULL,
  `file8` varchar(255) DEFAULT NULL,
  `file9` varchar(255) DEFAULT NULL,
  `file10` varchar(255) DEFAULT NULL,
  `filelist1` text,
  `filelist2` text,
  `filelist3` text,
  `filelist4` text,
  `filelist5` text,
  `filelist6` text,
  `filelist7` text,
  `filelist8` text,
  `filelist9` text,
  `filelist10` text,
  `link1` varchar(10) DEFAULT NULL,
  `link2` varchar(10) DEFAULT NULL,
  `link3` varchar(10) DEFAULT NULL,
  `link4` varchar(10) DEFAULT NULL,
  `link5` varchar(10) DEFAULT NULL,
  `link6` varchar(10) DEFAULT NULL,
  `link7` varchar(10) DEFAULT NULL,
  `link8` varchar(10) DEFAULT NULL,
  `link9` varchar(10) DEFAULT NULL,
  `link10` varchar(10) DEFAULT NULL,
  `linklist1` text,
  `linklist2` text,
  `linklist3` text,
  `linklist4` text,
  `linklist5` text,
  `linklist6` text,
  `linklist7` text,
  `linklist8` text,
  `linklist9` text,
  `linklist10` text,
  `php` text,
  `html` text,
  `article_id` int(11) NOT NULL,
  `modultyp_id` int(11) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `slice_prior` (`article_id`,`prior`,`modultyp_id`),
  KEY `clang` (`clang`),
  KEY `article_id` (`article_id`),
  KEY `find_slices` (`clang`,`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_article_slice` WRITE;
/*!40000 ALTER TABLE `rex_article_slice` DISABLE KEYS */;
INSERT INTO `rex_article_slice` VALUES
  (1,0,1,1,'Internet Professionell lobt REDAXO','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',1,1,1324156319,1324157571,'admin','admin',0),
  (2,0,1,2,'\"Mit kaum einer anderen Redaktionssoftware ist es so mühelos möglich, wirklich valide und barrierefreie Websites zu erstellen. Gerade die extreme Anpassungsfähigkeit an die verschiedenen Bedürfnisse ist eine der großen Stärken dieses Redaktionssystems.\"\r\n\r\n\"Dank des Cachings und des insgesamt sehr schlanken Cores (1,5 MB) sind REDAXO-Websites normalerweise sehr schnell. Im Vergleich zu anderen Content-Management-Systemen beeindruckt bei REDAXO vor allem die Flexibilität und Anpassungsfähigkeit.\"','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',1,3,1324156612,1324157581,'admin','admin',0),
  (3,0,1,1,'An dieser Stelle möchten wir auch einmal Danke für die vielen Anregungen, Kritiken, Ideen, Bugmeldungen, Wünsche usw. sagen:','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,3,1324157809,1324157809,'admin','admin',0),
  (4,0,1,2,'Das REDAXO Team','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,1,1324157821,1324157821,'admin','admin',0),
  (5,0,1,3,'Jan Kristinus\r\n\"www.yakamara.de\":http://www.yakamara.de','Jan Kristinus','','','','','','','l','','','','','','','','','','','','jan.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324158120,1324158192,'admin','admin',0),
  (6,0,1,4,'Markus Staab\r\n\"www.redaxo.org\":http://www.redaxo.org','Markus Staab','','','','','','','l','','','','','','','','','','','','markus.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324158253,1324158253,'admin','admin',0),
  (7,0,1,5,'Thomas Blum\r\n\"www.blumbeet.com\":http://www.blumbeet.com','Thomas Blum','','','','','','','l','','','','','','','','','','','','thomas.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324158772,1324158772,'admin','admin',0),
  (8,0,1,9,'Sandra Hundacker [Demo]','Sandra Hundacker','','','','','','','l','','','','','','','','','','','','sandra.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324158802,1324158802,'admin','admin',0),
  (9,0,1,6,'Peter Bickel\r\n\"www.polarpixel.de\":http://www.polarpixel.de','Peter Bickel','','','','','','','l','','','','','','','','','','','','peter.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324158918,1324158918,'admin','admin',0),
  (10,0,1,7,'GN2 netwerk\r\n\"www.gn2-netwerk.de\":http://www.gn2-netwerk.de','GN2 netwerk','','','','','','','l','','','','','','','','','','','','gn2.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324158963,1324159090,'admin','admin',0),
  (11,0,1,8,'Joachim Dörr [Demo]\r\n\"www.joachim-doerr.com\":http://www.joachim-doerr.com','Joachim Dörr','','','','','','','l','','','','','','','','','','','','team-bild.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',2,2,1324159018,1324159117,'admin','admin',0),
  (12,0,1,1,'Was ist REDAXO','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',7,1,1324159191,1324159191,'admin','admin',0),
  (13,0,1,2,'h2. Was ist REDAXO\r\n\r\nREDAXO ist ein Content Management System für individuelle, vielfältige und flexible Web-Lösungen.\r\n\r\nh2. Merkmale:\r\n\r\n* Trennung von Inhalt und Layout mittels Templates\r\n* Die Verwaltung von mehrsprachigen Webseiten ist gegeben\r\n* Der Inhalt setzt sich aus verschiedenen Modulen zusammen\r\n* Keine Grenzen bei der Erstellung von Modulen\r\n* Systemunabhängiges sowie plattformübergreifendes Arbeiten über den Webbrowser\r\n* Linkmanagement\r\n* Keine Einschränkungen bei der Entwicklung von barrierefreiem Webdesign\r\n* Aufnahme von Metadaten für Suchmaschinen möglich\r\n* Suchfunktionen können integriert werden\r\n* Rechteverteilung sind möglich\r\n* Medienverwaltung über Medienpool (HTML, XML, PDF, MP3, DOC, JPEG, GIF etc.)\r\n* Import / Export Funktion ermöglicht Projektsicherung\r\n* Einbindung von Erweiterungen/Addons für unterschiedlichste Funktionen, auf der REDAXO-Website gibt es zahlreiche Addons zum Download\r\n* REDAXO passt sich dem eigenen Wissensstand an\r\n* REDAXO basiert auf PHP / MySQL ','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',7,3,1324159209,1324159209,'admin','admin',0),
  (14,0,1,1,'Für wen ist REDAXO','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',8,1,1324159233,1324159233,'admin','admin',0),
  (15,0,1,2,'h2. Für wen ist REDAXO\r\n\r\nREDAXO ist für alle, die Websites erstellen, und für Nutzer, die mittels einer erstellten REDAXO-Website Inhalte verwalten.\r\n\r\nh2. Für Webdesigner und Administratoren - Erstellung und Gestaltung des Systems\r\n\r\nREDAXO ist kein Plug+Play-System! REDAXO ist für individuelle Lösungen gedacht, daher sind Kenntnisse von HTML und CSS unabdingbar, und Grundkenntnisse in PHP sollten ebenfalls vorhanden sein. REDAXO lässt sich sehr einfach installieren; Anpassungen sind leicht zu realisieren.\r\n\r\nDer größte Vorteil von REDAXO liegt in der Flexibilität. Die Ausgabe von REDAXO ist komplett beeinflussbar, das heißt: Mittels HTML und CSS lassen sich alle denkbaren Designs umsetzen. Ebenso kann man ohne weiteres barrierefreie Websites realisieren.\r\n\r\nh2. Für Redakteure - Verwaltung von Inhalten\r\n\r\nRedakteure brauchen zur Bedienung von REDAXO keine besonderen Kenntnisse. Der Schulungsaufwand ist auch für unerfahrene Nutzer gering. Die Struktur ist klar und übersichtlich aufgebaut, ohne erschlagende Funktionsfülle. Der Administrator kann dem Redakteur die Möglichkeiten und Rechte zur Hand geben, mit denen er alle gewünschten Inhalte und Einstellungen vornehmen kann, ohne Gefahr zu laufen, die Seite zu zerstören.\r\n','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',8,3,1324159245,1324159245,'admin','admin',0),
  (16,0,1,1,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','7','','','','','','','','','','','','','','','','','','','','','',3,5,1324159263,1324159263,'admin','admin',0),
  (17,0,1,1,'Features','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',9,1,1324159440,1324159440,'admin','admin',0),
  (18,0,1,2,'h2. Frei gestaltbar\r\n\r\nMittels HTML/CSS und Templates lassen sich alle denkbaren Designs umsetzen - selbst die Administrationsoberfläche (Backend). Die Ausgabe von REDAXO ist komplett beeinflussbar. \r\n\r\nh2. Suchmaschinenfreundlich\r\n\r\nDurch URL-Rewriting, individuelle Meta-Infos und freie Templategestaltung ist die Optimierung für Suchmaschinen gewährleistet.\r\n\r\nh2. Barrierearm und BITV-konform\r\n\r\nREDAXO erfüllt alle Grundvoraussetzungen, die für eine barrierefreie und BITV-konforme Website notwendig sind. Das Frontend kann der jeweilige Ersteller der Seiten barrierearm gestalten. Das Backend ist ebenfalls barrierearm ausgelegt und kann über Accesskeys per Tastatur bedient werden.\r\n\r\nh2. Mehrsprachigkeit und UTF8\r\n\r\nMit REDAXO können auch mehrsprachige Websites mit exotischen Zeichensätzen angeboten werden. Die Unterstützung von UTF8 erleichtert die Sprachverwaltung - egal, ob englisch, italienisch, französisch, eine Sprache aus dem osteuropäischen oder asiatischen Sprachraum. \r\n\r\nh2. Zukunftssicher für Monitor, PDA, Handy ...\r\n\r\nDa die Ausgabe von REDAXO komplett beeinflussbar ist, kann die Website auch für alternative Geräte maßgeschneidert werden.\r\n\r\nh2. Module und Addons\r\n\r\nErweiterungen können als Module/Addons zum Einsatz kommen. Wie alle guten Content Management Systeme unterstützt auch REDAXO benutzerdefinierte Erweiterungen.\r\n\r\nh2. Benutzerverwaltung\r\n\r\nEs können ausgefeilte Benutzerrechte vergeben werden.\r\n\r\nh2. Modularer Aufbau der Inhalte\r\n\r\nDie Inhalte einer Seite werden modular aus verschiedenen Blöcken zusammengesetzt, die man vergleichsweise leicht selbst programmieren kann - z.B. Überschrift, Text, Bildergalerie, Formular ... Dies ermöglicht eine außergewöhnlich flexible Erstellung des Inhalts.\r\n\r\nh2. Erhalt der Design-Vorgaben\r\n\r\nInhalte und Präsentation werden getrennt voneinander gespeichert. Folglich wird der gesamte Inhalt aller Redakteure in einem Design konsistent ausgegeben.\r\n\r\nh2. Standortunabhängige Pflege der Seiten\r\n\r\nREDAXO funktioniert auf jedem Rechner der mit dem Internet verbunden ist. Seiten können von jedem Ort und zu jeder Zeit über einen Browser bearbeitet werden.\r\n\r\nh2. Textinhalte editieren\r\n\r\nDem Redakteur können zum Editieren der Inhalte verschiedene Möglichkeiten zur Verfügung gestellt werden - von festen Texteingabefeldern über den Textile-Editor bis hin zu Wysiwyg-Editoren wie TinyMCE.\r\n','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',9,3,1324159451,1324159451,'admin','admin',0),
  (19,0,1,1,'Screenshots','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',10,1,1324159498,1324159498,'admin','admin',0),
  (20,0,1,2,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','screenshot_benutzerverwaltu.png,screenshot_content.png,screenshot_content_edit.png,screenshot_kategorie_edit.png,screenshot_medienpool.png,screenshot_module.png','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',10,4,1324159545,1324159545,'admin','admin',0),
  (21,0,1,1,'Erste Schritte','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,1,1324195642,1324195642,'admin','admin',0),
  (22,0,1,2,'Auf den folgenden Seiten erfahren Sie mehr über die Installation von REDAXO auf dem Webserver Ihrer Wahl. Überprüfen Sie vor Beginn die Systemvoraussetzungen für Ihr Webpaket und lernen Sie mehr über die ersten Schritte mit einem flexiblen Content-Management-System.','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,3,1324195657,1324195657,'admin','admin',0),
  (23,0,1,3,'Links zu REDAXO','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,1,1324195674,1324195674,'admin','admin',0),
  (24,0,1,1,'*redaxo.org* | \"http://www.redaxo.org\":http://www.redaxo.org\r\nAktuelle Informationen zu den aktuellen Versionen, die Basis-Intallation und Updates erhalten Sie auf der offiziellen REDAXO-Website.','REDAXO','','','','','','','l','','','','','','','','','','','','rex.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',11,2,1324195811,1324195811,'admin','admin',0),
  (25,0,1,1,'*redaxo.org/de/doku* | \"http://www.redaxo.org/de/doku/\":http://www.redaxo.org/de/doku/\r\nDie Online-Dokumentation von REDAXO\r\nDie Dokumentation ist in acht Teile gegliedert um so den Redakteur, dem Einsteiger und dem Profi die geeigneten Anlaufstellen zu bieten. Danke Dagmar - dag.\r\n','Doku','','','','','','','l','','','','','','','','','','','','doku.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',12,2,1324195852,1324195852,'admin','admin',0),
  (26,0,1,1,'*redaxo.org/de/wiki* | \"http://www.redaxo.org/de/wiki/\":http://www.redaxo.org/de/wiki/\r\nIm Wiki stehen Ideen und konkrete Beispiele mit Beschreibungen, ohne Prüfung seitens der Entwickler. Danke Sven - koala.','Wiki','','','','','','','l','','','','','','','','','','','','wiki.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',13,2,1324195886,1324195886,'admin','admin',0),
  (27,0,1,1,'*redaxo.org/de/forum* | \"http://www.redaxo.org/de/forum/\":http://www.redaxo.org/de/forum/\r\nEine der ersten Anlaufstellen für Support, Fragen, Tipps und \"Insider-Wissen\" ist das Forum zu REDAXO. Danke Community.','Forum','','','','','','','l','','','','','','','','','','','','forum.gif','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',14,2,1324196023,1324196023,'admin','admin',0),
  (28,0,1,1,'Nehmen Sie Kontakt auf','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',6,1,1324196075,1324196075,'admin','admin',0),
  (29,0,1,2,'*REDAXO*\r\nYakamara Media GmbH & Co. KG\r\nAnsprechpartner: Jan Kristinus\r\nKaiserstraße 69\r\n60329 Frankfurt \r\nTel.: 0611-504.599.21\r\nTel.: 0611-504.599.30\r\n\r\n\"www.redaxo.org\":http://www.redaxo.org\r\n\"www.yakamara.de\":http://www.yakamara.de\r\n\r\n*Programmierung der Demo / HTML Layout*\r\nSandra Hundacker [hundertmorgen] - hundertmorgen\r\n\r\n*REDAXO Agenturen auf redaxo.org*\r\n\"www.redaxo.org\":http://www.redaxo.org/de/redaxo/agenturen-support/\r\n\r\n*Fotos*\r\n\"www.photocase.com\":http://www.photocase.com\r\n','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',6,3,1324196556,1324196556,'admin','admin',0),
  (30,0,1,4,'5','100','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',4,7,1324196607,1324196607,'admin','admin',0),
  (31,0,1,1,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',16,1,1324197299,1324197299,'admin','admin',0),
  (32,0,1,2,'Was ist das Besondere an REDAXO?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',16,1,1324197318,1324197318,'admin','admin',0),
  (33,0,1,3,'REDAXO unterscheidet sich von anderen Systemen auf den ersten Blick durch sein sehr schlicht gehaltenes und auf grafische Dekoration verzichtendes Backend. So finden sich auch weniger technikorientierte Anwender schnell in den Funktionen zurecht. Der Administrator kann je nach Bedarf einzelne Funktionen zu- oder abschalten. Dadurch ist REDAXO selbst für Netzauftritte mit wenigen Seiten einsetzbar, ohne durch seine Funktionsfülle den eigentlichen Seiteninhalt zu dominieren. (Quelle: Wikipedia)\r\n\r\n\"zurück\":redaxo://5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',16,3,1324197441,1324197441,'admin','admin',0),
  (34,0,1,1,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',17,1,1324197549,1324197549,'admin','admin',0),
  (35,0,1,2,'Was sollte einen dazu bewegen, REDAXO zu nutzen?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',17,1,1324197557,1324197557,'admin','admin',0),
  (36,0,1,3,'Zitat aus dem Forum: „Die nette Community und der gute Support. ;-) “\r\n\r\n\"zurück\":redaxo://5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',17,3,1324197602,1324197602,'admin','admin',0),
  (37,0,1,1,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',18,1,1324197638,1324197638,'admin','admin',0),
  (38,0,1,2,'Wann wird der Einsatz von REDAXO empfohlen?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',18,1,1324197645,1324197645,'admin','admin',0),
  (39,0,1,3,'Die Praxis hat gezeigt, dass REDAXO für Webauftritte bis ca. 3000 Seiten ohne Probleme oder Geschwindigkeitseinbußen einsetzbar ist. Je nach Konzept der Website und den Seiteninhalten können es bei optimaler Planung aber auch mehr werden. (Quelle: Wikipedia)\r\n\r\n\"zurück\":redaxo://5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',18,3,1324197672,1324197672,'admin','admin',0),
  (40,0,1,1,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',19,1,1324200198,1324200198,'admin','admin',0),
  (41,0,1,2,'Wie viele Internetpräsentationen wurden bereits mit REDAXO erstellt?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',19,1,1324200207,1324200207,'admin','admin',0),
  (42,0,1,3,'Es gibt ca. 1690 gelistete Referenzen auf der REDAXO-Seite (Stand 12.2011). Man kann jedoch davon ausgehen, daß die wirkliche Anzahl ein vielfaches davon ist.\r\n\r\n\"zurück\":redaxo://5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',19,3,1324200274,1324200436,'admin','admin',0),
  (43,0,1,1,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',20,1,1324200288,1324200288,'admin','admin',0),
  (44,0,1,2,'Welche Kenntnisse brauche ich, um mit REDAXO arbeiten zu können?','h2','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',20,1,1324200310,1324200310,'admin','admin',0),
  (45,0,1,3,'REDAXO basiert auf PHP und Mysql. Kenntnisse in dieser Sprache und im Umgang mit der Datenbank sind zwar zu empfehlen, aber nicht unbedingt erforderlich. Anhand der Demo-Versionen kann man bereits eigene Webseiten erstellen und dabei lernen, das System zu nutzen.\r\n\r\n\"zurück\":redaxo://5','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',20,3,1324200333,1324200333,'admin','admin',0),
  (46,0,1,1,'FAQ','h1','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',5,1,1324200453,1324200453,'admin','admin',0),
  (47,0,1,2,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',5,6,1324200495,1324200495,'admin','admin',0),
  (48,0,1,3,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','screenshot_benutzerverwaltu.png,screenshot_content.png','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',10,4,1324215592,1324215807,'admin','admin',0);
/*!40000 ALTER TABLE `rex_article_slice` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_clang`;
CREATE TABLE `rex_clang` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `rex_clang` WRITE;
/*!40000 ALTER TABLE `rex_clang` DISABLE KEYS */;
INSERT INTO `rex_clang` VALUES
  (0,'deutsch',0);
/*!40000 ALTER TABLE `rex_clang` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_config`;
CREATE TABLE `rex_config` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(75) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`cid`),
  UNIQUE KEY `unique_key` (`namespace`,`key`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_config` WRITE;
/*!40000 ALTER TABLE `rex_config` DISABLE KEYS */;
INSERT INTO `rex_config` VALUES
  (28,'rex-core','package-config','{\"be_dashboard\":{\"install\":false,\"status\":false,\"plugins\":{\"rss_reader\":{\"install\":false,\"status\":false},\"userinfo\":{\"install\":false,\"status\":false},\"version_checker\":{\"install\":false,\"status\":false}}},\"be_search\":{\"install\":true,\"status\":true},\"be_style\":{\"install\":true,\"status\":true,\"plugins\":{\"agk_skin\":{\"install\":false,\"status\":false},\"redaxo\":{\"install\":true,\"status\":true}}},\"compat\":{\"install\":false,\"status\":false,\"plugins\":{\"3.x\":{\"install\":false,\"status\":false},\"4.x\":{\"install\":false,\"status\":false}}},\"cronjob\":{\"install\":false,\"status\":false,\"plugins\":{\"article_status\":{\"install\":false,\"status\":false},\"optimize_tables\":{\"install\":false,\"status\":false}}},\"debug\":{\"install\":false,\"status\":false},\"import_export\":{\"install\":true,\"status\":true},\"install\":{\"install\":true,\"status\":true,\"plugins\":{\"core\":{\"install\":true,\"status\":true},\"packages\":{\"install\":true,\"status\":true}}},\"media_manager\":{\"install\":true,\"status\":true},\"mediapool\":{\"install\":true,\"status\":true},\"metainfo\":{\"install\":true,\"status\":true},\"modules\":{\"install\":true,\"status\":true},\"phpmailer\":{\"install\":false,\"status\":false},\"structure\":{\"install\":true,\"status\":true,\"plugins\":{\"content\":{\"install\":true,\"status\":true},\"linkmap\":{\"install\":true,\"status\":true}}},\"templates\":{\"install\":true,\"status\":true},\"tests\":{\"install\":false,\"status\":false},\"textile\":{\"install\":true,\"status\":true},\"tinymce\":{\"install\":false,\"status\":false},\"url_rewrite\":{\"install\":false,\"status\":false},\"users\":{\"install\":true,\"status\":true},\"version\":{\"install\":false,\"status\":false}}'),
  (25,'rex-core','package-order','[\"users\",\"textile\",\"tests\",\"modules\",\"templates\",\"mediapool\",\"structure\",\"structure\\/content\",\"structure\\/linkmap\",\"import_export\",\"metainfo\",\"be_search\",\"be_style\",\"be_style\\/agk_skin\",\"media_manager\",\"be_style\\/redaxo\",\"install\\/core\",\"install\\/packages\",\"install\"]'),
  (21,'media_manager','jpg_quality','85');
/*!40000 ALTER TABLE `rex_config` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_media`;
CREATE TABLE `rex_media` (
  `media_id` int(11) NOT NULL AUTO_INCREMENT,
  `re_media_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `attributes` text,
  `filetype` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `originalname` varchar(255) DEFAULT NULL,
  `filesize` varchar(255) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `revision` int(11) NOT NULL,
  `med_description` text,
  `med_copyright` text,
  PRIMARY KEY (`media_id`),
  KEY `re_media_id` (`re_media_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_media` WRITE;
/*!40000 ALTER TABLE `rex_media` DISABLE KEYS */;
INSERT INTO `rex_media` VALUES
  (1,0,7,'','text/plain','ajs.js','ajs.js','10396',0,0,'',1324201368,1324201368,'admin','admin',0,'',''),
  (2,0,7,'','text/plain','ajs_fx.js','ajs_fx.js','3192',0,0,'',1324201368,1324201368,'admin','admin',0,'',''),
  (3,0,7,'','image/gif','g_close.gif','g_close.gif','541',25,30,'',1324201368,1324201368,'admin','admin',0,'',''),
  (4,0,7,'','text/plain','gb_scripts.js','gb_scripts.js','11908',0,0,'',1324201368,1324201368,'admin','admin',0,'',''),
  (5,0,7,'','text/x-c','gb_styles.css','gb_styles.css','2574',0,0,'',1324201368,1324201368,'admin','admin',0,'',''),
  (6,0,7,'','image/gif','indicator.gif','indicator.gif','8238',100,100,'',1324201368,1324201368,'admin','admin',0,'',''),
  (7,0,7,'','text/html','loader_frame.html','loader_frame.html','2084',0,0,'',1324201368,1324201368,'admin','admin',0,'',''),
  (8,0,7,'','image/gif','next.gif','next.gif','528',25,30,'',1324201368,1324201368,'admin','admin',0,'',''),
  (9,0,7,'','image/gif','prev.gif','prev.gif','525',25,30,'',1324201368,1324201368,'admin','admin',0,'',''),
  (10,0,7,'','image/gif','w_close.gif','w_close.gif','74',11,11,'',1324201368,1324201368,'admin','admin',0,'',''),
  (11,0,2,'','image/gif','bg_header.gif','bg_header.gif','3656',630,137,'Hintergrund Header',1324201918,1324548157,'admin','admin',0,'',''),
  (12,0,2,'','image/gif','bg_nav.gif','bg_nav.gif','929',208,217,'Hintergrund Navigation vertikal',1324201918,1324548184,'admin','admin',0,'',''),
  (13,0,2,'','image/gif','bg_unav.gif','bg_unav.gif','6494',208,172,'Hintergrund unterhalb Navigation',1324201918,1324548169,'admin','admin',0,'',''),
  (14,0,2,'','image/gif','button.gif','button.gif','132',20,60,'Rollover Buttons',1324201919,1324547920,'admin','admin',0,'',''),
  (16,0,2,'','image/gif','linie_main_block.gif','linie_main_block.gif','744',3,211,'Hintergrund content default (Linie)',1324201919,1324548080,'admin','admin',0,'',''),
  (17,0,2,'','image/gif','linie_start_block.gif','linie_start_block.gif','11484',313,245,'Hintergrund content start ',1324201919,1324548138,'admin','admin',0,'',''),
  (18,0,2,'','image/jpeg','main_teaser.jpg','main_teaser.jpg','41139',630,220,'Teaser Inhaltsseiten',1324201919,1324548204,'admin','admin',0,'',''),
  (19,0,2,'','image/jpeg','start_bg_header.gif','start_bg_header.gif','7846',630,137,'Header Start',1324201919,1324548125,'admin','admin',0,'',''),
  (20,0,2,'','image/jpeg','start_teaser.jpg','start_teaser.jpg','39416',630,217,'Teaser Startseite',1324201919,1324548213,'admin','admin',0,'',''),
  (21,0,1,'','text/x-c','content.css','content.css','4681',0,0,'content.css',1324201938,1324547800,'admin','admin',0,'Inhaltselemente global',''),
  (22,0,1,'','text/x-c','default.css','default.css','1535',0,0,'default.css',1324201938,1324547817,'admin','admin',0,'besondere Elemente - Inhaltsseiten',''),
  (23,0,1,'','text/x-c','main.css','main.css','3707',0,0,'main.css',1324201938,1324547877,'admin','admin',0,'Grundraster Layout',''),
  (24,0,1,'','text/x-c','navigation.css','navigation.css','2546',0,0,'navigation.css',1324201938,1324547859,'admin','admin',0,'Navigationselemente',''),
  (25,0,1,'','text/x-c','start.css','start.css','1462',0,0,'start.css',1324201938,1324547834,'admin','admin',0,'besondere Elemente - Startseite',''),
  (26,0,5,'','image/gif','doku.gif','doku.gif','1713',79,79,'Doku',1324201976,1324547287,'admin','admin',0,'',''),
  (27,0,5,'','image/gif','forum.gif','forum.gif','1765',79,79,'Forum',1324201976,1324547295,'admin','admin',0,'',''),
  (28,0,5,'','image/gif','rex.gif','rex.gif','1805',79,79,'Redaxo',1324201976,1324547302,'admin','admin',0,'',''),
  (29,0,5,'','image/gif','wiki.gif','wiki.gif','1650',79,79,'Wiki',1324201976,1324547309,'admin','admin',0,'',''),
  (30,0,6,'','image/png','screenshot_benutzerverwaltu.png','screenshot_benutzerverwaltu.png','105370',980,650,'Benutzerverwaltung',1324202006,1324547573,'admin','admin',0,'',''),
  (31,0,6,'','image/png','screenshot_content.png','screenshot_content.png','86971',980,650,'Inhaltsansicht: Editiermodus',1324202006,1324547584,'admin','admin',0,'',''),
  (32,0,6,'','image/png','screenshot_content_edit.png','screenshot_content_edit.png','71997',980,650,'Inhaltsansicht: Einfachen Text editieren',1324202006,1324547553,'admin','admin',0,'',''),
  (33,0,6,'','image/png','screenshot_kategorie_edit.png','screenshot_kategorie_edit.png','80826',980,650,'Kategorieansicht: Kategorienamen editieren',1324202006,1324547601,'admin','admin',0,'',''),
  (34,0,6,'','image/png','screenshot_medienpool.png','screenshot_medienpool.png','116370',980,650,'Medienpool zur Verwaltung von Dateien/Bildern',1324202006,1324547616,'admin','admin',0,'',''),
  (35,0,6,'','image/png','screenshot_module.png','screenshot_module.png','96277',980,650,'Modulansicht',1324202006,1324547633,'admin','admin',0,'',''),
  (36,0,4,'','image/gif','gn2.gif','gn2.gif','3518',79,79,'gn2 Team',1324202047,1324547712,'admin','admin',0,'',''),
  (37,0,4,'','image/gif','jan.gif','jan.gif','2730',79,79,'Jan',1324202047,1324547719,'admin','admin',0,'',''),
  (38,0,4,'','image/gif','markus.gif','markus.gif','2798',79,79,'Markus',1324202047,1324547726,'admin','admin',0,'',''),
  (39,0,4,'','image/gif','peter.gif','peter.gif','1196',79,79,'Peter',1324202047,1324547734,'admin','admin',0,'',''),
  (40,0,4,'','image/gif','sandra.gif','sandra.gif','1154',79,79,'Sandra',1324202047,1324547741,'admin','admin',0,'',''),
  (41,0,4,'','image/gif','team-bild.gif','team-bild.gif','680',79,79,'Platzhalter',1324202047,1324547753,'admin','admin',0,'',''),
  (42,0,4,'','image/gif','thomas.gif','thomas.gif','2806',79,79,'Thomas',1324202047,1324547761,'admin','admin',0,'',''),
  (43,0,4,'','image/gif','wolfgang.gif','wolfgang.gif','2781',79,79,'Wolfgang',1324202047,1324547770,'admin','admin',0,'',''),
  (44,0,2,'','image/gif','redaxo_logo_klein.gif','redaxo_logo_klein.gif','3472',186,80,'Logo',1324202084,1324547934,'admin','admin',0,'',''),
  (46,0,2,'','image/gif','raquo.gif','raquo.gif','164',7,6,'Pfeil',1324547653,1324547942,'admin','admin',0,'','');
/*!40000 ALTER TABLE `rex_media` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_media_category`;
CREATE TABLE `rex_media_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `re_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `attributes` text,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `re_id` (`re_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_media_category` WRITE;
/*!40000 ALTER TABLE `rex_media_category` DISABLE KEYS */;
INSERT INTO `rex_media_category` VALUES
  (1,'Layout - css',0,'|',1324158285,1324158285,'admin','admin','',0),
  (2,'Layout - images',0,'|',1324158290,1324158290,'admin','admin','',0),
  (3,'Inhalt - images',0,'|',1324158299,1324158299,'admin','admin','',0),
  (4,'Team',3,'|3|',1324158319,1324158319,'admin','admin','',0),
  (5,'Schritte',3,'|3|',1324158322,1324158322,'admin','admin','',0),
  (6,'Screenshots',3,'|3|',1324158329,1324158329,'admin','admin','',0),
  (7,'Bildgalerie',1,'|1|',1324158371,1324158371,'admin','admin','',0);
/*!40000 ALTER TABLE `rex_media_category` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_media_manager_type_effects`;
CREATE TABLE `rex_media_manager_type_effects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `effect` varchar(255) NOT NULL,
  `parameters` text NOT NULL,
  `prior` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `createuser` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_media_manager_type_effects` WRITE;
/*!40000 ALTER TABLE `rex_media_manager_type_effects` DISABLE KEYS */;
INSERT INTO `rex_media_manager_type_effects` VALUES
  (1,1,'resize','{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"200\",\"rex_effect_resize_height\":\"200\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"not_enlarge\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}',1,1319833448,'admin',1319833448,'admin'),
  (2,2,'resize','{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"600\",\"rex_effect_resize_height\":\"600\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"not_enlarge\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}',1,1319833472,'admin',1319833448,'admin'),
  (3,3,'resize','{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"80\",\"rex_effect_resize_height\":\"80\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"not_enlarge\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}',1,1319833496,'admin',1319833448,'admin'),
  (4,4,'resize','{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"246\",\"rex_effect_resize_height\":\"246\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"not_enlarge\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}',1,1319833418,'admin',1319833448,'admin'),
  (5,5,'resize','{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"246\",\"rex_effect_resize_height\":\"246\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"not_enlarge\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}',1,1319833532,'admin',1319833448,'admin'),
  (6,6,'resize','{\"rex_effect_crop\":{\"rex_effect_crop_width\":\"\",\"rex_effect_crop_height\":\"\",\"rex_effect_crop_offset_width\":\"\",\"rex_effect_crop_offset_height\":\"\",\"rex_effect_crop_hpos\":\"center\",\"rex_effect_crop_vpos\":\"middle\"},\"rex_effect_filter_blur\":{\"rex_effect_filter_blur_amount\":\"80\",\"rex_effect_filter_blur_radius\":\"8\",\"rex_effect_filter_blur_threshold\":\"3\"},\"rex_effect_filter_sharpen\":{\"rex_effect_filter_sharpen_amount\":\"80\",\"rex_effect_filter_sharpen_radius\":\"0.5\",\"rex_effect_filter_sharpen_threshold\":\"3\"},\"rex_effect_flip\":{\"rex_effect_flip_flip\":\"X\"},\"rex_effect_header\":{\"rex_effect_header_download\":\"open_media\",\"rex_effect_header_cache\":\"no_cache\"},\"rex_effect_insert_image\":{\"rex_effect_insert_image_brandimage\":\"\",\"rex_effect_insert_image_hpos\":\"left\",\"rex_effect_insert_image_vpos\":\"top\",\"rex_effect_insert_image_padding_x\":\"-10\",\"rex_effect_insert_image_padding_y\":\"-10\"},\"rex_effect_mediapath\":{\"rex_effect_mediapath_mediapath\":\"\"},\"rex_effect_mirror\":{\"rex_effect_mirror_height\":\"\",\"rex_effect_mirror_set_transparent\":\"colored\",\"rex_effect_mirror_bg_r\":\"\",\"rex_effect_mirror_bg_g\":\"\",\"rex_effect_mirror_bg_b\":\"\"},\"rex_effect_resize\":{\"rex_effect_resize_width\":\"250\",\"rex_effect_resize_height\":\"\",\"rex_effect_resize_style\":\"maximum\",\"rex_effect_resize_allow_enlarge\":\"not_enlarge\"},\"rex_effect_workspace\":{\"rex_effect_workspace_width\":\"\",\"rex_effect_workspace_height\":\"\",\"rex_effect_workspace_hpos\":\"left\",\"rex_effect_workspace_vpos\":\"top\",\"rex_effect_workspace_set_transparent\":\"colored\",\"rex_effect_workspace_bg_r\":\"\",\"rex_effect_workspace_bg_g\":\"\",\"rex_effect_workspace_bg_b\":\"\"}}',1,1324160022,'admin',1324160022,'admin');
/*!40000 ALTER TABLE `rex_media_manager_type_effects` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_media_manager_types`;
CREATE TABLE `rex_media_manager_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_media_manager_types` WRITE;
/*!40000 ALTER TABLE `rex_media_manager_types` DISABLE KEYS */;
INSERT INTO `rex_media_manager_types` VALUES
  (1,1,'rex_mediapool_detail','Zur Darstellung von Bildern in der Detailansicht im Medienpool'),
  (2,1,'rex_mediapool_maximized','Zur Darstellung von Bildern im Medienpool wenn maximiert'),
  (3,1,'rex_mediapool_preview','Zur Darstellung der Vorschaubilder im Medienpool'),
  (4,1,'rex_mediabutton_preview','Zur Darstellung der Vorschaubilder in REX_MEDIA_BUTTON[]s'),
  (5,1,'rex_medialistbutton_preview','Zur Darstellung der Vorschaubilder in REX_MEDIALIST_BUTTON[]s'),
  (6,0,'gallery_overview','Zur Anzeige der Screenshot-Gallerie');
/*!40000 ALTER TABLE `rex_media_manager_types` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_metainfo_params`;
CREATE TABLE `rex_metainfo_params` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `prior` int(10) unsigned NOT NULL,
  `attributes` text NOT NULL,
  `type` int(10) unsigned DEFAULT NULL,
  `default` varchar(255) NOT NULL,
  `params` text,
  `validate` text,
  `callback` text,
  `restrictions` text,
  `createuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `updatedate` int(11) NOT NULL,
  PRIMARY KEY (`field_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_metainfo_params` WRITE;
/*!40000 ALTER TABLE `rex_metainfo_params` DISABLE KEYS */;
INSERT INTO `rex_metainfo_params` VALUES
  (1,'translate:pool_file_description','med_description',1,'',2,'','','','','','admin',1189343866,'admin',1189344596),
  (2,'translate:pool_file_copyright','med_copyright',2,'',1,'','','','','','admin',1189343877,'admin',1189344617),
  (3,'translate:online_from','art_online_from',1,'',10,'','','','','','admin',1189344934,'admin',1189344934),
  (4,'translate:online_to','art_online_to',2,'',10,'','','','','','admin',1189344947,'admin',1189344947),
  (5,'translate:description','art_description',3,'',2,'','','','','','admin',1189345025,'admin',1189345025),
  (6,'translate:keywords','art_keywords',4,'',2,'','','','','','admin',1189345068,'admin',1189345068),
  (7,'translate:metadata_image','art_file',5,'',6,'','','','','','admin',1189345109,'admin',1189345109),
  (8,'translate:teaser','art_teaser',6,'',5,'','','','','','admin',1189345182,'admin',1189345182),
  (9,'translate:header_article_type','art_type_id',7,'size=1',3,'','Standard|Zugriff fuer alle','','','','admin',1191963797,'admin',1191964038);
/*!40000 ALTER TABLE `rex_metainfo_params` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_metainfo_type`;
CREATE TABLE `rex_metainfo_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `dbtype` varchar(255) NOT NULL,
  `dblength` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_metainfo_type` WRITE;
/*!40000 ALTER TABLE `rex_metainfo_type` DISABLE KEYS */;
INSERT INTO `rex_metainfo_type` VALUES
  (1,'text','text',0),
  (2,'textarea','text',0),
  (3,'select','varchar',255),
  (4,'radio','varchar',255),
  (5,'checkbox','varchar',255),
  (10,'date','text',0),
  (13,'time','text',0),
  (11,'datetime','text',0),
  (12,'legend','text',0),
  (6,'REX_MEDIA_BUTTON','varchar',255),
  (7,'REX_MEDIALIST_BUTTON','text',0),
  (8,'REX_LINK_BUTTON','varchar',255),
  (9,'REX_LINKLIST_BUTTON','text',0);
/*!40000 ALTER TABLE `rex_metainfo_type` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_module`;
CREATE TABLE `rex_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `output` text NOT NULL,
  `input` text NOT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `attributes` text,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_module` WRITE;
/*!40000 ALTER TABLE `rex_module` DISABLE KEYS */;
INSERT INTO `rex_module` VALUES
  (1,'01 - Headline',0,'<REX_VALUE[2]>REX_VALUE[1]</REX_VALUE[2]>','&Uuml;berschrift:<br />\r\n<input type=\"text\" size=\"50\" name=\"VALUE[1]\" value=\"REX_VALUE[1]\" />\r\n<select name=\"VALUE[2]\" >\r\n<?php\r\nforeach (array(\"h1\",\"h2\",\"h3\",\"h4\",\"h5\",\"h6\") as $value) {\r\n  echo \'<option value=\"\'.$value.\'\" \';\r\n  \r\n  if ( \"REX_VALUE[2]\"==\"$value\" ) {\r\n    echo \'selected=\"selected\" \';\r\n  }\r\n  echo \'>\'.$value.\'</option>\';\r\n}\r\n?>\r\n</select>','admin','',1324155740,0,'',0),
  (2,'01 - Text und/oder Bild [textile]',0,'<?php\r\n\r\nif(rex_addon::get(\"textile\")->isAvailable())\r\n{\r\n  echo \'<div class=\"team\">\';\r\n\r\n  //  Ausrichtung des Bildes \r\n  if (\"REX_VALUE[9]\" == \"l\") $float = \"floatLeft\";\r\n  if (\"REX_VALUE[9]\" == \"r\") $float = \"floatRight\";\r\n\r\n  //  Wenn Bild eingefuegt wurde, Code schreiben \r\n  $file = \"\";\r\n  if (\"REX_FILE[1]\" != \"\") $file = \'<div class=\"\'.$float.\'\"><img src=\"\'.rex_path::media(\'REX_FILE[1]\').\'\" title=\"\'.\"REX_VALUE[2]\".\'\" alt=\"\'.\"REX_VALUE[2]\".\'\" /></div>\';\r\n\r\n  $textile = \'\';\r\n  if(REX_IS_VALUE[1])\r\n  {\r\n    $textile = htmlspecialchars_decode(\"REX_VALUE[1]\");\r\n    $textile = str_replace(\"<br />\",\"\",$textile);\r\n    $textile = rex_textile::parse($textile);\r\n    $textile = str_replace(\"###\",\"&#x20;\",$textile);\r\n  } \r\n  print $file.$textile;\r\n\r\n  echo \'</div>\';\r\n}\r\nelse\r\n{\r\n  echo rex_view::warning(\'Dieses Modul ben&ouml;tigt das \"textile\" Addon!\'); \r\n}\r\n\r\n?>','<?php\r\nif(rex_addon::get(\"textile\")->isAvailable())\r\n{\r\n?>\r\n\r\n<strong>Fliesstext</strong>:<br />\r\n<textarea name=\"VALUE[1]\" cols=\"80\" rows=\"10\" class=\"inp100\">REX_HTML_VALUE[1]</textarea>\r\n<br /><br />\r\n\r\n<strong>Artikelfoto</strong>:<br />\r\nREX_MEDIA_BUTTON[1]\r\n<?php\r\nif (\"REX_FILE[1]\" != \"\") {\r\n        echo \"<br /><strong>Vorschau</strong>:<br />\";\r\n  echo \"<img src=\".rex_path::media(\'REX_FILE[1]\').\"><br />\";\r\n}\r\n?>\r\n\r\n<br />\r\n<strong>Title des Fotos</strong>:<br />\r\n<input type=\"text\" name=\"VALUE[2]\" value=\"REX_VALUE[2]\" size=\"80\" class=\"inp100\" />\r\n<br /><br />\r\n\r\n<strong>Ausrichtung des Artikelfotos</strong>:<br />\r\n<select name=\"VALUE[9]\" class=\"inp100\">\r\n  <option value=\'l\' <?php if (\"REX_VALUE[9]\" == \'l\') echo \'selected\'; ?>>links vom Text</option>\r\n</select><br />\r\n<br />\r\n<br />\r\n\r\n<?php\r\nrex_a79_help_overview(); \r\n\r\n}\r\nelse\r\n{\r\n  echo rex_view::warning(\'Dieses Modul ben&ouml;tigt das \"textile\" Addon!\'); \r\n}\r\n\r\n?>','admin','admin',1324155808,1324286515,'',0),
  (3,'01 - Text [textile]',0,'<?php\r\n\r\nif(rex_addon::get(\"textile\")->isAvailable())\r\n{\r\n  // Fliesstext \r\n  $textile = \'\';\r\n  if(REX_IS_VALUE[1])\r\n  {\r\n    $textile = htmlspecialchars_decode(\"REX_VALUE[1]\");\r\n    $textile = str_replace(\"<br />\",\"\",$textile);\r\n    $textile = rex_textile::parse($textile);\r\n    $textile = str_replace(\"###\",\"&#x20;\",$textile);\r\n    print \'<div class=\"txt-img\">\'. $textile . \'</div>\';\r\n  } \r\n}\r\nelse\r\n{\r\n  echo rex_view::warning(\'Dieses Modul ben&ouml;tigt das \"textile\" Addon!\'); \r\n}\r\n\r\n?>','<?php\r\nif(rex_addon::get(\"textile\")->isAvailable())\r\n{\r\n?>\r\n<strong>Fliesstext</strong>:<br />\r\n<textarea name=\"VALUE[1]\" cols=\"80\" rows=\"10\" class=\"inp100\">REX_HTML_VALUE[1]</textarea>\r\n\r\n<?php\r\n\r\nrex_a79_help_overview(); \r\n\r\n}else\r\n{\r\n  echo rex_warning(\'Dieses Modul benötigt das \"textile\" Addon!\');\r\n}\r\n\r\n?>','admin','admin',1324155826,1324286521,'',0),
  (4,'03 - Bildgalerie',0,'<?php\r\n\r\n// if is_prev_a_galery nothing get the javascript\r\nif(rex::getProperty(\'is_prev_a_galery\') == \'\')\r\n{\r\n?>\r\n<script type=\"text/javascript\">\r\nvar GB_ROOT_DIR = \"<?php echo rex_path::media(); ?>\";\r\n</script>\r\n<script type=\"text/javascript\" src=\"<?php echo rex_path::media(\'ajs.js\'); ?>\"></script>\r\n<script type=\"text/javascript\" src=\"<?php echo rex_path::media(\'ajs_fx.js\'); ?>\"></script>\r\n<script type=\"text/javascript\" src=\"<?php echo rex_path::media(\'gb_scripts.js\'); ?>\"></script>\r\n<link type=\"text/css\" href=\"<?php echo rex_path::media(\'gb_styles.css\'); ?>\" rel=\"stylesheet\" />\r\n\r\n<?php\r\n}\r\n?>\r\n\r\n<div class=\"galerie\">\r\n\r\n<?php\r\n\r\n$pics_string = \"REX_MEDIALIST[1]\";\r\nif($pics_string != \'\')\r\n{\r\n  $i = 1;\r\n  $pics = explode(\',\',$pics_string);\r\n\r\n  foreach($pics as $pic)\r\n  {\r\n    echo \'<div class=\"image\">\';\r\n\r\n    $title = \'\';\r\n    if ($file = rex_media::getMediaByFileName($pic)) $title = $file->getTitle();\r\n\r\n    echo \'<a href=\"\'.rex_path::media($pic).\'\" rel=\"gb_imageset[galerieREX_SLICE_ID]\">\r\n    <img src=\"\'.rex_path::frontendController().\'?rex_media_type=gallery_overview&rex_media_file=\'.$pic.\'\" title=\"\'.$title.\'\" alt=\"\'.$title.\'\" /></a>\';\r\n\r\n    echo \'<p>\'.$title.\'</p>\';\r\n    echo \'</div>\';\r\n\r\n    if($i % 2 == 0)\r\n      echo \'<div class=\"clearer\"></div>\';\r\n\r\n    $i++;  \r\n  }\r\n}\r\n\r\n// get info first is true\r\nrex::setProperty(\'is_prev_a_galery\',\'true\');\r\n\r\n?></div>','Bitte Bilder auswählen:\r\n<br />REX_MEDIALIST_BUTTON[1]\r\n<br /><br />','admin','admin',1324155851,1324548836,'',0),
  (5,'04 - Artikelweiterleitung',0,'<?php\r\n\r\nif(rex::isBackend() === false && REX_ARTICLE_ID != REX_LINK_ID[1])\r\n{\r\n  if (REX_LINK_ID[1] != 0)\r\n  {\r\n   rex_redirect(REX_LINK_ID[1], $REX[\'CUR_CLANG\']);\r\n  }\r\n} else\r\n{\r\n  echo \"Weiterleitung zu <a href=\'index.php?page=content&article_id=REX_LINK_ID[1]&mode=edit\'>Artikel           REX_LINK[1]</a>\";\r\n}\r\n\r\n?>','Artikel, zu dem Weitergeleitet werden soll:<br /><br />\r\nREX_LINK_BUTTON[1]','admin','admin',1324155893,1324159400,'',0),
  (6,'05 - Artikelliste',0,'<?php\r\n\r\n$cat = rex_category::getCategoryById($this->getValue(\"category_id\"));\r\n$article = $cat->getArticles();\r\n\r\nif (is_array($article)) \r\n{\r\n  foreach ($article as $var) \r\n  {\r\n    $articleId = $var->getId();\r\n    $articleName = $var->getName();\r\n    $articleDescription = $var->getValue(\"art_description\");\r\n    if (!$var->isStartpage()) \r\n    {\r\n      echo \'<a href=\"\'.rex_getUrl($articleId).\'\" class=\"faq\">\'.$articleName.\'</a><br />\';\r\n    }\r\n  }\r\n}\r\n\r\n?>','','admin','admin',1324155916,1324200633,'',0),
  (7,'05 - Kategorienliste',0,'<?php\r\n\r\n$cat = rex_category::getCategoryById($this->getValue(\'category_id\'));\r\n$cats = $cat->getChildren();\r\n\r\n$itemsPerSide = \"REX_VALUE[1]\";\r\n$wordsPerArticle = \"REX_VALUE[2]\";\r\n\r\nif (is_array($cats))\r\n{\r\n  $i = 0;\r\n  foreach ($cats as $cat)\r\n  {\r\n    $i += 1;\r\n    if ($i <= $itemsPerSide)\r\n    {\r\n      if ($cat->isOnline())\r\n      {\r\n\r\n        $catId = $cat->getId();\r\n        $catName = $cat->getName();\r\n        $article = $cat->getArticles();\r\n\r\n        if (is_array($article))\r\n        {\r\n          foreach ($article as $var)\r\n          {\r\n            $articleId = $var->getId();\r\n            $articleName = $var->getName();\r\n            $art = new rex_article($articleId);\r\n            $articleContent = $art->getArticle();\r\n\r\n            $articleContent = trim($articleContent);\r\n            $articleContent = str_replace(\'</p>\', \' </p>\', $articleContent);\r\n            $articleContent = str_replace(\'<br />\', \' <br />\', $articleContent);\r\n\r\n            $articlePPath = $REX[\'MEDIAFOLDER\'] . \'files/\' . $var->getValue(\'file\');\r\n\r\n            $output = \'\';\r\n            $words = explode(\' \', $articleContent);\r\n            $wordsCount = count($words);\r\n\r\n            if ($wordsCount < $wordsPerArticle)\r\n              $wEnd = $wordsCount;\r\n            else\r\n              $wEnd = $wordsPerArticle;\r\n\r\n            for ($w = 0; $w < $wEnd; $w++)\r\n            {\r\n              $output .= $words[$w] . \' \';\r\n            }\r\n\r\n            $output = trim($output);\r\n\r\n            $isCloseParagraph = substr($output, -4);\r\n            $isCloseDiv = substr($output, -10);\r\n            $link = \'<a href=\"\' . rex_getUrl($articleId) . \'\" class=\"more\"> ...mehr</a>\';\r\n            $newString = $link . \'</p>\';\r\n\r\n            if ($isCloseParagraph == \'</p>\')\r\n            {\r\n              $output = substr_replace($output, $newString, -4);\r\n            }\r\n            elseif ($isCloseDiv == \'</p></div>\')\r\n            {\r\n              $output = substr_replace($output, $newString.\'</div>\', -10);\r\n            }\r\n            else\r\n            {\r\n              $output .= $newString;\r\n            }\r\n\r\n            // print \'<h2>\'.$articleName.\'</h2>\';\r\n            print \'<div class=\"txt-img\">\' . $output . \'</div>\';\r\n\r\n          }\r\n        }\r\n      }\r\n    }\r\n  }\r\n}\r\n?>','<?php\r\n\r\n//---MODULE BY----------------------\r\n//-- Wegener IT\r\n//-- Mattias Beckmann\r\n//-- www.wegener-it.de\r\n//----------------------------------\r\n\r\n?>\r\n\r\n<strong>Anzahl der Artikel pro Seite</strong><br />\r\n<input name=\"VALUE[1]\" value=\"REX_VALUE[1]\" class=\"inp100\" />\r\n\r\n<br /><br />\r\n<strong>Anzahl der Wörter pro Artikel</strong><br />\r\n<input name=\"VALUE[2]\" value=\"REX_VALUE[2]\" class=\"inp100\" />\r\n\r\n<br />','admin','admin',1324155954,1324196734,'',0);
/*!40000 ALTER TABLE `rex_module` ENABLE KEYS */;
UNLOCK TABLES;

DROP TABLE IF EXISTS `rex_module_action`;
CREATE TABLE `rex_module_action` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
DROP TABLE IF EXISTS `rex_template`;
CREATE TABLE `rex_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content` text,
  `active` tinyint(1) DEFAULT NULL,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` int(11) NOT NULL,
  `updatedate` int(11) NOT NULL,
  `attributes` text,
  `revision` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

LOCK TABLES `rex_template` WRITE;
/*!40000 ALTER TABLE `rex_template` DISABLE KEYS */;
INSERT INTO `rex_template` VALUES
  (1,'','default','<?php\r\n\r\nerror_reporting(E_ALL ^ E_NOTICE);\r\n\r\n// ------ OOSTARTARTICLE\r\n$OOStartArticle = rex_article::getArticleById(rex::getProperty(\'start_article_id\'), rex_clang::getId());\r\n\r\n// ------ DEFAULT DESCRIPTION/KEYWORDS\r\n$meta_beschreibung = $OOStartArticle->getValue(\"art_description\");\r\n$meta_suchbegriffe = $OOStartArticle->getValue(\"art_keywords\");\r\n\r\n// ------ FROM THIS DESCRIPTION/KEYWORDS\r\nif($this->getValue(\"art_description\") != \"\") {\r\n  $meta_beschreibung = $this->getValue(\"art_description\");\r\n}\r\nif($this->getValue(\"art_keywords\") != \"\") {\r\n  $meta_suchbegriffe = $this->getValue(\"art_keywords\");\r\n}\r\n\r\n?><!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\r\n  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\r\n\r\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"de\" lang=\"de\">\r\n  <head>\r\n    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />\r\n    <title><?php print rex::getProperty(\'server\').\' | \'.$this->getValue(\"name\"); ?></title>\r\n    <meta name=\"keywords\" content=\"<?php print htmlspecialchars($meta_suchbegriffe); ?>\" />\r\n    <meta name=\"description\" content=\"<?php print htmlspecialchars($meta_beschreibung); ?>\" />\r\n\r\n    <link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo rex_path::media(\'main.css\'); ?>\" media=\"screen\" />\r\n    <link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo rex_path::media(\'navigation.css\'); ?>\" media=\"screen\" />\r\n    <link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo rex_path::media(\'content.css\'); ?>\" media=\"screen\" />\r\n    <link rel=\"stylesheet\" type=\"text/css\" href=\"<?php echo rex_path::media(\'default.css\'); ?>\" media=\"screen\" />\r\n\r\n  </head>\r\n  <body class=\"mainPage\">\r\n    <div>\r\n      <a name=\"top\" id=\"top\"></a>\r\n    </div>\r\n    <div id=\"site-content\">\r\n      <div id=\"column\">\r\n        \r\n        <div id=\"header\">\r\n          <div id=\"logo\">\r\n            <a href=\"<?php echo rex_getUrl(rex::getProperty(\'start_article_id\'), rex_clang::getId()); ?>\" title=\"Zur&uuml;ck zur Startseite\">REDAXO Demo</a>\r\n          </div>\r\n        </div>\r\n        \r\n        <div id=\"content\">\r\n          <div id=\"main-content\">\r\n            \r\n            <div id=\"nav\">\r\n              REX_TEMPLATE[2]\r\n              <p class=\"copy\">&copy; by <a href=\"http://www.redaxo.org\">REDAXO</a></p>\r\n            </div>\r\n            \r\n            <div id=\"main\">\r\n              <div id=\"main-block\">\r\n                <div id=\"main-teaser\">\r\n                  Slogan: Einfach, flexibel, sinnvoll\r\n                </div>\r\n                \r\n                <div id=\"main-content-block\">\r\n                  REX_TEMPLATE[3]\r\n                  REX_ARTICLE[]\r\n                </div>\r\n              </div>\r\n            </div>\r\n            <br class=\"clear\" />\r\n            \r\n          </div>\r\n        </div>\r\n        \r\n        <div id=\"footer\">\r\n          <p class=\"floatRight\"><a href=\"http://www.redaxo.org\">REDAXO CMS</a> - SIMPLE DEMO | XHTML 1.0 Strict | pictures by <a href=\"http://www.photocase.com\">photocase.com</a></p>\r\n          <br class=\"clear\" />\r\n        </div>\r\n        \r\n      </div>\r\n    </div>\r\n\r\n    <div style=\"display:none;\">Eigene Templates sind besser - REDAXO</div>\r\n  </body>\r\n</html>',1,'admin','admin',1324208423,1324208423,'{\"ctype\":[],\"modules\":{\"1\":{\"all\":\"1\"}},\"categories\":{\"all\":\"1\"}}',0),
  (2,'','Navigation: Links','<?php\r\n\r\n// navigation generator erstellen\r\n$nav = rex_ooNavigation::factory();\r\n\r\n// ---------- HEAD NAVI\r\n\r\n// Beginne in der Wurzelkategorie\r\n// 1 Ebene Tief\r\n// Nicht aufklappen (hier egal da nur 1 Ebene)\r\n// Offline ausblenden\r\n\r\n$category_id = 0;\r\n$depth = 3;\r\n$open = FALSE;\r\n$ignore_offlines = TRUE;\r\n\r\necho $nav->get($category_id, $depth, $open, $ignore_offlines);\r\n\r\n?>',0,'admin','admin',1324157196,1324157196,'{\"ctype\":[],\"modules\":{\"1\":{\"all\":\"1\"}},\"categories\":{\"all\":\"1\"}}',0),
  (3,'','Navigation: Breadcrumb','<?php\r\n\r\n// ---------- BREADCRUMB\r\n\r\n// Beginne in der Wurzelkategorie\r\n// 1 Ebene Tief\r\n// Nicht aufklappen (hier egal da nur 1 Ebene)\r\n// Offline ausblenden \r\n\r\n$category_id = 0;\r\n$includeCurrent = TRUE;\r\n\r\n// navigation generator erstellen\r\n$nav = rex_ooNavigation::factory();\r\n\r\n$breadcrumb_output = \'<div id=\"breadcrumb\">\';\r\nif (rex_clang::getId() == 1) {\r\n  $breadcrumb_output .= \'<p>You are here:</p>\'. $nav->getBreadcrumb(\'Startpage\', $includeCurrent, $category_id);\r\n} else {\r\n  $breadcrumb_output .= \'<p>Sie befinden sich hier:</p>\'. $nav->getBreadcrumb(\'Startseite\', $includeCurrent, $category_id);\r\n}\r\n$breadcrumb_output .= \'</div>\';\r\n\r\necho $breadcrumb_output;\r\n\r\n?>',0,'admin','admin',1324157190,1324157190,'{\"ctype\":[],\"modules\":{\"1\":{\"all\":\"1\"}},\"categories\":{\"all\":\"1\"}}',0);
/*!40000 ALTER TABLE `rex_template` ENABLE KEYS */;
UNLOCK TABLES;
