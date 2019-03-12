Changelog
=========

Version 2.5.0 – 12.03.2019
--------------------------

### Neu

* Über `installer_ignore` in der `package.yml` können Addons Ordner/Dateien angeben, die beim Upload ausgeschlossen werden sollen (@schuer)
* AddOn-Beschreibung und Versions-Beschreibungen werden als Markdown geparst (@tbaddade, @bloep)
* Erläuterung zu Backup-Option in den Einstellungen (@schuer)

### Bugfixes

* Beim Update wurde mit den alten Requirements/Conflicts geprüft, wenn die neue Version keine Requirements/Conflicts mehr enthielt (@gharlan)
* Besseres Escaping mittels `rex_escape` (@bloep)


Version 2.4.0 – 05.06.2018
--------------------------

### Neu

* "Veröffentlicht am" wird ausgegeben, Addons können danach sortiert werden (@bloep)


Version 2.3.0 – 21.12.2017
--------------------------

### Neu

* CSRF-Schutz (@gharlan)

### Bugfix

* Der Opcache wurde nicht gelöscht nach Updates (@gharlan)


Version 2.2.0 – 04.10.2017
--------------------------

### Neu

* Autofokus auf Suchefeld (@skerbis)

### Bugfixes

* Bei Reload nach dem Download/Update kam es zu einer Exception (@gharlan)


Version 2.1.2 – 14.02.2017
--------------------------

### Bugfixes

* Beim Updaten kam es teilweise zum Fehler „Cannot use string offset as an array“
* htmlspecialchars fehlte an vielen Stellen, dadurch konnten teilweise die Addondetails nicht aufgerufen werden
* Wenn die Einstellungen nicht gespeichert werden konnten, kam es trotzdem zur Erfolgsmeldung


Version 2.1.1 – 15.07.2016
--------------------------

### Bugfixes

* Nach Core-Updates wurde teilweise die gleiche Version direkt wieder zum Updaten angezeigt


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* Bei Einschränkung der Addons wird nur noch Hauptversion betrachtet (5.x)


Version 2.0.2 – 09.02.2016
--------------------------

### Bugfixes

* Bessere Darstellung der alten und neuen Versionsnummer beim Überschreiben (Upload) einer Addon-Version


Version 2.0.1 – 22.01.2016
--------------------------

### Bugfixes

* Beim Download/Update großer Addons kam es teilweise zu einer Fehlermeldung
* Api-Login und Api-Key werden nicht mehr in rex_config (in der DB) gespeichert, und sind somit nicht mehr im Export enthalten
