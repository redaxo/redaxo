Changelog
=========

Version 2.8.0 – 10.03.2020
--------------------------

### Neu

* Default-Verschlüsselung auf "keine" gesetzt (da "Auto" bei manchen Providern Probleme verursachte) (@skerbis)
* Log-Subpage auch im Addon verfügbar (nicht nur unter System/Log) (@skerbis)
* Erläuterungstexte verbessert (@skerbis)

### Bugfixes

* Debug-Ausgabe erscheint nun im Panel (@skerbis)


Version 2.7.0 – 02.02.2020
--------------------------

### Neu

* Vorhandene Log-Funktion (Ablegen der ganzen Mails) umbenannt in Archivierung (@skerbis)
* Neue Log-Funktion mit Zeit, Absender, Empfänger, Betreff und Meldung in Logdatei; optional für alle Mails, oder nur bei Fehlern, oder ganz deaktiviert (@skerbis)
* Hinweis in Readme, dass über SMTP keine leeren Bodys möglich sind (@skerbis)


Version 2.6.0 – 20.08.2019
--------------------------

### Neu

* Default-Timeout auf 10s gesetzt (statt 5min) (@skerbis)
* Englische Übersetzung der Readme (@skerbis)


Version 2.5.1 – 16.03.2019
--------------------------

### Bugfixes

* E-Mail-Benachrichtigung bei Fehlern wurde teilweise kontinuierlich bei jedem Seitenaufruf verschickt (@skerbis)


Version 2.5.0 – 12.03.2019
--------------------------

### Neu

* E-Mail-Benachrichtigung bei Fehlern (@phoebusryan, @skerbis)
* AutoTLS kann aktiviert/deaktiviert werden (@skerbis)
* SMTP-Einstellungen werden erst angezeigt, wenn Option gewählt. (@skerbis)
* Benutzername und Passwort werden erst angezeigt wenn Option gewählt (@skerbis)
* Bessere Test-Mails, Button-Verhalten geändert in "Speichern und testen" (@skerbis)
* Debug-Meldungen werden in Sprache des Benutzers ausgegeben (@skerbis)
* Aktualisierung Hilfe/Doku (@skerbis)


Version 2.4.1 – 10.12.2018
--------------------------

### Security

* Update des phpmailers wg. Sicherheitslücken


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

