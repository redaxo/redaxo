Changelog
=========

Version 2.1.0 – 04.10.2017
--------------------------

### Neu

* Neue Option im Backup-Cronjob zum automatischen Löschen alter Backups (@alexplusde, @gharlan)

### Bugfixes

* Multi-Select-Feld für Tabellen-Auswahl war zu klein (@alexplusde)


Version 2.0.4 – 14.02.2017
--------------------------

### Security

* Für Backend-Benutzer mit Zugriff auf die Export-Page waren SQL-Injections möglich

### Bugfixes

* Wenn beim Export keine Tabelle ausgewählt wurde, kam die falsche Fehlermeldung (@zorker)
* Bei fehlenden Dateirechten wurde eine Meldung ohne Übersetzung angezeigt (@skerbis)
* Im Mailtext des Backup-Cronjobs erschien `&quot;`
* Richtiger Umgang mit Foreign Keys in Backup-Dateien


Version 2.0.1 – 12.01.2016
--------------------------

### Bugfixes

* Datei-Export funktionierte nicht
