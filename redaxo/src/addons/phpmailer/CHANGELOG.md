Changelog
=========

Version 2.4.0 – 05.06.2018
--------------------------

### Neu

* Update phpmailer 6.0.5 (@gharlan)
* Mail-Log ist default nicht mehr aktiviert (@skerbis)
* Aktualisierung Hilfe/Doku (@skerbis)


Version 2.3.0 – 21.12.2017
--------------------------

### Neu

* Update auf PHPMailer 6.0.2 (@skerbis)
* SMTP-Passwort-Feld wird nicht mehr im Klartext angezeigt (@metaphon)


Version 2.2.0 – 04.10.2017
--------------------------

### Neu

* Möglichkeit den Versand zu testen (Testmail) (@skerbis)
* Mail-Log kann global und pro Versand deaktiviert werden (@dergel)
* Bessere Hilfe und Beispiele (@skerbis)
* Einstellungen in Spalten (@skerbis)
* `X-Mailer`-Header auf "REXMailer" gesetzt (@skerbis)

### Bugfixes

* Das SMTP-Passwort-Feld wurde teilweise vorbelegt mit dem im Browser gespeicherten Passwort (@gharlan)


Version 2.1.2 – 08.01.2017
--------------------------

### Security

* Update phpmailer auf 5.2.21 mit Security-Fixes (remote code execution vulnerability)
  Mehr Informationen: https://github.com/PHPMailer/PHPMailer/wiki/About-the-CVE-2016-10033-and-CVE-2016-10045-vulnerabilities


Version 2.1.1 – 15.07.2016
--------------------------

* Update phpmailer auf 5.2.16
* SMTP-Debug kann in Backend konfiguriert werden (@alexplusde)


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* Es werden Backups der gesendeten Mails erstellt (@phoebusryan)

### Bugfixes

* Header X-Priority wird standardmäßig nicht mehr gesetzt, da die Mails sonst eher als Spam gewertet werden (@skerbis)

