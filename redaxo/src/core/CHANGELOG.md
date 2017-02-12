Changelog
=========

Version 5.3.0 – XX.XX.2017
--------------------------

### Security

* „Security“-Abschnitte beachten in den Addons backup, structure und phpmailer

### Neu

* Updates: symfony/yaml (3.2.3), parsedown (1.6.1)
* Neue Funktion dump() zur Debug-Ausgabe von Variablen (macht nur Ausgaben für im Backend eingeloggte Admins)
* Schönere Fehlerseiten über Whoops
* Bessere Sichtbarkeit des Safe-Modes
* Neue Backendsprache Schwedisch (Jürgen Weiss)
* Paginierung: Anzahl der ausgegeben Seiten wird beschränkt
* Beim Löschen von Packages wird deren Data-Ordner nicht mehr gelöscht
* Bei mehr als 3 Sprachen wird die Sprachauswahl als Dropdown angezeigt
* rex_sql: 
    - Bei Abfragen kann PDO::MYSQL_ATTR_USE_BUFFERED_QUERY deaktiviert werden
    - Neue Methode getMysqlErrnp() um MySQL-spezifischen Error-Code abzufragen
    - Bei Exceptions werden die PDO-Originalexception mit übergeben
* mbstring-Extension optional (durch Polyfill)
* Cache-Buster für Backend-Assets
* Performance-Verbesserungen

### Bugfixes

* Setup:
    - Funktionierte nicht mit PHP 7.1
    - Reine SQL-Exporte (ohne Dateiarchiv) konnten nicht zum Import ausgewählt werden
    - tokenizer-Extension wurde nicht überprüft
* Autoloader: Klassen konnten teilweise nicht gefunden werden, wenn sie sehr lange Strings enthielten
* rex_sql: 
    - Tabellen-/Feldnamen werden korrekt escaped
    - getErrno und getError lieferten teilweise nicht das richtige Ergebnis
    - Debug-Infos wurden im Fehlerfall nicht ausgegeben
* rex_form: Errorcode-spezifische Fehlermeldungen wurden nicht getriggert
* rex_list: Bei der Query durften keine Leerzeichen vor dem Begin stehen (" SELECT ...")
* package.yml: 
    - `null`-Werte führten zu Fehler
    - Bessere Fehlermeldung, wenn `requires` kein Array ist
* EP RESPONSE_SHUTDOWN blockiert nicht mehr das Beenden der Antwort und die Session
* Profil: Durch überflüssiges `</div>` wurde Footer falsch angezeigt (@aeberhard)
* Beim Auslesen von `php.ini`-Werten kam es teilweise zu einer Notice
* Bei Seitenaufrufen über pjax wurde die Skriptzeit nicht aktualisiert
* Teilweise kam es zum JS-Fehler „Permission denied to access property winObjCounter“ (@ynamite)


Version 5.2.0 – 15.07.2016
--------------------------

### Neu

* PHP-Mindestversion 5.5.9
* Updates: jquery 2.2.4, symfony/yaml 3.1.2
* Markdown-Parser integriert:
    - README.md wird in Addonhilfe ausgegeben, falls help.php nicht vorhanden
    - Markdown-Dateien können in package.yml als Subpages definiert werden
* Neues leeres "project"-Addon, wo projektspezifische Dinge abgelegt werden können
* getSupportPage() bei Packages liefert die URL immer mit Protokoll (http://) (**BC-BREAK**)
* rex_sql: $pdo-Property als protected statt private
* rex_formatter: Auch DATETIME-Strings werden unterstützt

### Bugfixes

* Navigationspunkte werden nun ohne Beachtung Groß-/Kleinschreibung sortiert
* "Last-Modified"-Header wurden fälschlicherweise nicht als GMT geliefert (@rosserl)
* Nicht-installierte Addons mit leerer package.yml erzeugten eine Warnung (@aeberhard)
* Im Safemode hat der Autoloader unter bestimmten Bedingungen einen Fehler geworfen
* Login-Seite wurde mit 401-Header ausgeliefert, was mit machen Proxys inkompatibel ist
* pjax-Timeout verdoppelt, um doppelte Ausführungen von Aktionen zu vermeiden
* Fehlermeldung bei Reload nach (De)Aktivierung von Packages beseitigt
* rex_finder: Funktionierte auf manchen NAS nicht, da dort `fnmatch` nicht zur Verfügung steht
* rex_socket: Bei POST-Requests waren keine anderen Content-Types als "application/x-www-form-urlencoded" möglich
* rex_string: highlight() erzeugte teils doppelte Zeilenumbrüche
* rex_form: Felder innerhalb von Container-Fields konnten nicht gleich heißen wie vorhandene normale Felder


Version 5.1.0 – 24.03.2016
---------------------------

### Neu

* Core-Update wieder über Setup möglich, als Alternative zum Installer
* Status (online/offline) für Sprachen
* Neue REX_VAR: REX_CLANG[]
* Neue EPs: REX_FORM_GET und REX_LIST_GET
* Extension Points: Es können Extensions direkt für mehrere EPs (Array) registriert werden
* Im Backend wird der Header "X-Frame-Options: SAMEORIGIN" gesetzt
* Favicon im Backend
* Popups (Medienpool/Linkmap): Bereits offene Fenster wurden teilweise nicht korrekt weiter verwendet
* rex_context: Methoden fromGet() und fromPost() ergänzt
* rex_list: Methode getArrayValue() ergänzt
* rex_view: Bei doppelten CSS/JS-Dateien wird Exception geworfen
* Update jQuery (2.2.1), Bootstrap (3.3.6), Font-Awesome (4.5.0), symfony/yaml (3.0.3)

### Bugfixes

* rex_stream (Einbindung Module/Templates): Funktionierte auf manchen Servern (Strato) nicht
* Durch falsche Cache-Header wurden im IE teilweise veraltete Backend-Pages angezeigt
* ETag-Header wurden im Chrome nicht gesetzt
* Es gab Umlaut-Probleme, wenn die Default-Collation der DB nicht UTF-8 ist
* Autoloader: Es werden relative Pfade statt absolute gecacht, dadurch keine Probleme beim Verschieben des Projektordners
* PAGE_TITLE_SHOWN: Das Ergebnis des EPs wurde vor dem Titel angezeigt
* Subpages wurden nicht immer im richtigen Kontext (Addon/Plugin) geladen
* Hilfedateien: Sprachkeys der Packages waren nicht verfügbar, wenn das Package nicht aktiviert war
* Beim Reinstall/Update der Packages wurden die Ladereihenfolge nicht richtig aktualisert
* Packages konnten in package.yml nicht itemAttr und linkAttr setzen
* Der "Letzte Login" der Benutzer wurde nicht richtig erfasst
* rex_form: Feldnamen mit Sonderzeichen machten teilweise Probleme


Version 5.0.1 – 09.02.2016
---------------------------

### Security

* Im eingeloggten Bereich war eine SQL-Injection im Medienpool möglich
* Im eingeloggten Bereich war XSS im Medienpool möglich

### Bugfixes

* Fehlende Übersetzungen in Englisch ergänzt (@VIEWSION)
* Bei Überprüfung der DB-Verbindung wurde keine sinnvolle Fehlermeldung ausgegeben, wenn der Datenbankname fehlte
* Navigation: Teilweise erschienen die Addons über dem Hauptmenü bei Nicht-Admins
* Einbindung von Templates/Modulen (rex_stream) funktionierte auf manchen Servern nicht
* REX_VARs:
    - Verschachtelte REX_VARs funktionierten nicht bei Nutzung der globalen Args (ifempty, prefix etc.)
    - Teilweise verschwanden direkt auf die Vars folgende Zeilenumbrüche
* Der Error-Handler kam nicht mit den neuen Error-Klassen in PHP 7 klar
* rex_validator: Die verschiedenen Validierungstypen akzeptierten keine leeren Werte, dies muss nun explizit mit notEmpty geprüft werden
* rex_form: HTML5-Validierungen werden nun nicht mehr beim Löschen- und Abbrechen-Button ausgelöst
* Dropdowns als Dropups darstellen bei zuwenig Platz nach unten
