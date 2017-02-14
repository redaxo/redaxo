Changelog
=========

Version 2.2.0 – 14.02.2017
--------------------------

### Neu

* Neue Methode rex_media::getRootMedia()
* Die rex_media-Klasse ist leichter erweiterbar (@DanielWeitenauer)
* Beim Upload- und Sync-Formular steht die Kategorie-Auswahl ganz oben (da bei Kategoriewechsel die Seite sich aktualisiert um die für die Kategorie richtigen Metainfos anzuzeigen)

### Bugfixes

* Dateityp-Einschränkung funktionierte nicht richtig
* Medienpool-Popup ließ sich teilweise für die gleiche Ebene mehrfach öffnen
* Medienpool-Popup schloss sich teilweise nicht korrekt
* REX_MEDIA[]: Vorschau für SVGs funktionierte nicht
* Bei Nutzung des Medienpools über Editoren (redactor, markitup) konnten nach Wechsel der Subpage die Medien nicht mehr ausgewählt werden
* Benutzer mit eingeschränkten Medienrechten konnten niemals die Metainfos der Medien bearbeiten


Version 2.1.1 – 15.07.2016
--------------------------

### Bugfixes

* Wildes Auf- und Zuklapen der Vorschau beim Media-Button entfernt (@schuer)
* SVGs wurden nicht angezeigt


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* REX_MEDIA[]: "field"-Argument ergänzt um Metainfos der Medien auszulesen
* JS-Event rex:selectMedia bei Auswahl einer Datei im Medienpool

### Bugfixes

* Auf OS X war Synchronisation von Dateien mit Umlauten im Namen nicht möglich
* In Meldungen waren teilweise HTML-Tags sichtbar
* Suche in Unterkategorien verursachte Fehler
* Nach Editieren war kein Sprung in andere Kategorie möglich
* Formular konnte nicht mit Enter abgesendet werden


Version 2.0.1 – 09.02.2016
--------------------------

### Security

* Im eingeloggten Bereich war eine SQL-Injection möglich
* Im eingeloggten Bereich war XSS möglich
