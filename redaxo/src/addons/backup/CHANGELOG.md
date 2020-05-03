Changelog
=========

Version 2.6.0 – 10.03.2020
--------------------------

### Neu

* Backups werden nach Dateiname sortiert (@bloep)

### Bugfixes

* Backup-Cronjob: Die Mail-Checkbox war nicht direkt über dem Mailadress-Feld (@gharlan)


Version 2.5.1 – 02.02.2020
--------------------------

### Bugfixes

* `NULL`-Werte wurden nicht als solche exportiert, was zu Problemen bei den neuen Template-Keys führte (@gharlan)


Version 2.5.0 – 02.02.2020
--------------------------

### Neu

* Default-Dateiname: Datum vor REDAXO-Version für bessere Sortierung (@bloep)
* Cronjob: Tabellen können ausgeschlossen werden, User-Tabelle default nun mit im Backup (@alexplusde)
* Es wird davor gewarnt, dass Import von Backups älterer REDAXO- und Addon-Versionen zu Problemen führen können (@gharlan)
* Upload-Limits werden angezeigt (@skerbis)
* Wording optimiert (@marcohanke)

### Bugfixes

* Beim Datei-Import kam es mit PHP 7.4 zu Notices (@gharlan)


Version 2.4.0 – 20.08.2019
--------------------------

### Neu

* Speicheroptimierung beim Export (@staabm)


Version 2.3.0 – 12.03.2019
--------------------------

### Neu

* Performance-Verbesserungen beim Export (@staabm)

### Bugfixes

* Views nicht exportieren (@gharlan)


Version 2.2.2 – 10.07.2018
--------------------------

### Bugfixes

* Fehlermeldung ausgeben, wenn Datei nicht gelöscht werden kann wegen fehlender Schreibrechte (@staabm)


Version 2.2.1 – 05.06.2018
--------------------------

### Bugfixes

* Kompatibilität zu PHP 7.2 (@gharlan)
* Speicherbedarf beim Export reduziert (@staabm)
* Es wird sichergestellt, dass keine unvollständigen Backups erstellt werden (@staabm)
* Backup-Cronjob: Wenn die automatische Löschung aktiviert war, funktioniert der Mailversand nicht mehr (@staabm)
* EP `BACKUP_AFTER_DB_IMPORT`: Während der Ausführung war der Cache veraltet (@gharlan)


Version 2.2.0 – 21.12.2017
--------------------------

### Neu

* CSRF-Schutz (@gharlan)


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
