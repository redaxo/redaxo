Changelog
=========

Version 2.10.0 – 03.03.2021
---------------------------

### Neu

* Bei der Darstellung der Metabeschreibung im Backend werden HTML-Tags entfernt (@skerbis)


Version 2.9.1 – 25.01.2021
--------------------------

### Bugfixes

* `rex_media_category_select`:
    - `setRootId` funktionierte nur mit Root-Kategorien (@gharlan)
    - Bei eingeschränkten Kategorierechten fehlten berechtigte Kategorien, wenn für deren Root keine Berechtigung vorhanden ist (@gharlan)


Version 2.9.0 – 01.07.2020
--------------------------

### Neu

* Neuer EP `MEDIA_ADD`, über den neue Medien vor dem Speichern weiter validiert werden können (@portux)
* Neues Recht `media[sync]` um den Zugriff auf die Sync-Page explizit steuern zu können (@skerbis)


Version 2.8.1 – 08.05.2020
--------------------------

### Bugfixes

* Dateien synchronisieren: Button-Disabled-Status wurde nicht richtig gesetzt (@bloep)


Version 2.8.0 – 10.03.2020
--------------------------

### Neu

* Neue EPs: `MEDIA_CATEGORY_ADDED`, `MEDIA_CATEGORY_UPDATED` und `MEDIA_CATEGORY_DELETED` (@staabm)
* EPs `MEDIA_ADDED`/`MEDIA_UPDATED`: Parameter `category_id` wird übergeben (@staabm)

### Bugfixes

* Bessere Mime-Type-Erkennung durch neue Core-Funktion `rex_file::mimeType()` (@gharlan)
* Es kam zu doppelten Medien in der DB, wenn zu einem Medium die physische Datei fehlte und dann eine gleichnamige erneut hochgeladen wurde (@gharlan)


Version 2.7.0 – 02.02.2020
--------------------------

### Neu

* Bei (Re)Installation/Update wird `rex_sql_table` verwendet (@tbaddade)
* Beim Upload wird nicht mehr der gesendete Mimetype, sondern der durch `mime_content_type()` bestimmte Typ genommen (@bloep)

### Bugfixes

* `rex_media`: Bei `hasValue` konnte im Gegensatz zu `getValue` nicht der `med_`-Präfix für die Metainfos weggelassen werden (@bloep)
* `rex_media_category`: Wenn bei `getChildren`/`getMedia` ein leere Liste herauskam, wurde unnötig der Cache erneuert (@gharlan)
* Beim Upload kam es in PHP 7.4 teils zu Notices (@gharlan)


Version 2.6.1 – 01.11.2019
--------------------------

### Security

* XSS Sicherheitslücken behoben (Michel Pass und Mathias Niedung von Althammer & Kill, @gharlan)


Version 2.6.0 – 20.08.2019
--------------------------

### Neu

* Assets nutzen immutable cache (@staabm)


Version 2.5.0 – 12.03.2019
--------------------------

### Security

* Double extension vulnerablility behoben (@staabm)
* XSS Sicherheitslücken (Cross-Site-Scripting) behoben (@staabm)

### Neu

* Bessere Code-Struktur (@staabm)
* Lazy-Load der Bilder in der Liste (@staabm)
* Neuer EP: `MEDIA_MOVED` (@bloep)
* @-Zeichen wird in Dateinamen nicht mehr ersetzt (@tbaddade)
* Popup 75% Höhe statt fixen 800px (@schuer)
* Visuelles Feedback für "Datei übernehmen" (@schuer)
* Buttonleiste unterhalb der Liste am Viewport fixiert (sticky) (@schuer)
* Anzeige der ID (in eckigen Klammern) entfernt (@schuer)
* Medienkategorie erstellen/bearbeiten: Autofocus auf Namensfeld (@schuer)

### Bugfixes

* Nach Löschen aus der Detailansicht heraus kam fälschlich die Fehlermeldung "Datei wurde nicht gefunden" (@gharlan)


Version 2.4.3 – 01.10.2018
--------------------------

### Security

* XSS Sicherheitslücken (Cross-Site-Scripting) behoben (gemeldet von @Balis0ng, ADLab of VenusTech) (@bloep)


Version 2.4.2 – 10.07.2018
--------------------------

### Bugfixes

* Optionale MimeType-Whitelist funktionierte nicht (@dergel)


Version 2.4.1 – 21.06.2018
--------------------------

### Bugfixes

* Übersetzung bei Lösch-Fehlermeldung fehlte (falsche Keys) (@gharlan)


Version 2.4.0 – 05.06.2018
--------------------------

### Security

* Es wurden nur die Dateiendungen `.php`, `.php5`, `.php7` usw. geblockt, manche Server führen aber auch `.php56`, `.php71` usw aus, daher werden nun alle Dateiendungen der Form `.php*` geblockt (gemeldet von Matthias Niedung, HackerWerkstatt) (@gharlan)
* CSRF-Schutz (@dergel)

### Neu

* Optional kann eine Whitelist von MimeTypes definiert werden (@dergel, @gharlan)
* Lösschen von Medienkategorien kann per neuem EP `MEDIA_CATEGORY_IS_IN_USE` verhindert werden (@christophboecker)
* Neuer EP `MEDIA_DETAIL_SIDEBAR` (@christophboecker)
* Die Functions-Datei wird auch im Frontend eingebunden (@gharlan)
* `rex_mediapool_syncFile`: Userlogin kann angegeben werden (für Nutzung im Frontend) (@gharlan)

### Bugfixes

* EP `MEDIA_ADDED` wurde doppelt ausgeführt (@gharlan)
* Im Safari 11.1 konnten Medien nicht aktualisiert werden (ohne sie gleichzeitig auszutauschen) (@gharlan)
* `rex_mediapool_updateMedia`:
    - Beim Direktaufruf wurde der EP `MEDIA_UPDATED` nicht aufgerufen (@gharlan)
    - Parameter `$FILE` und `$userlogin` wurden nicht genutzt, stattdessen wurde hartkodiert mit `$_FILES['file_new']` gearbeitet (@gharlan)



Version 2.3.3 – 05.01.2018
--------------------------

### Security

* Kategorie-Namen wurden in der Breadcrumb-Navi ohne Escaping ausgegeben (XSS) (@dergel)


Version 2.3.2 – 21.12.2017
--------------------------

### Bugfixes

* Bei Einschränkung der Typen ist beim Upload die Klein/Großschreibung der Dateiendung nicht mehr relevant (@gharlan)
* In der Doctypes-Property fehlten "svg" und "mp4" (@alexplusde)


Version 2.3.1 – 04.10.2017
--------------------------

### Security

* Weitere Dateiendungen werden geblockt: .pht, .phar, .hh, .htaccess, .htpasswd (@gharlan)
* Bei Dateien, die mit einem Punkt beginnen, wird dieser beim Upload durch einen Unterstrich ersetzt (@gharlan)

### Bugfixes

* Benutzer mit eingeschränkten MP-Kategorie-Rechten
    - konnte nicht die Multi-Aktionen (schieben, löschen) ausführen (@gharlan)
    - konnten in "Keine Kategorie" hochladen (@gharlan)
* In der Doctypes-Property fehlte "jpeg" (@IngoWinter)
* Abhängigkeit zur fileinfo-Extension entfernt (@staabm)


Version 2.3.0 – 19.03.2017
--------------------------

### Neu

* Neue Klasse rex_media_category_service
* Kategorie-Auswahl über bootstrap-select mit Suchfeld (@skerbis)

### Bugfixes

* Bei Nutzung über Editoren (Redactor etc.) wurde der Link teilweise mehrfach eingefügt
* Dateien konnten nicht ausgetauscht werden, wenn die Extensions der beiden Dateien sich in der Klein-/Großschreibung unterschieden, auch jpg gegen jpeg und umgekehrt ging nicht
* Nach dem Austauschen einer Datei wurde anschließend teilweise noch die alte Datei aus dem Cache angezeigt
* Bei Medialists wurden die Medien im Chrome teils verzögert in die Liste übernommen
* Teilweise kam es zum JS-Fehler „Permission denied to access property winObjCounter“ (@ynamite)


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
