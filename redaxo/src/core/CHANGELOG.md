Changelog
=========

Version 5.5.0 – XX.XX.2017
--------------------------

### Security

* CSRF-Schutz (@gharlan):
    - für alle `rex_form` automatisch
    - für eigene Api-Function per Opt-in
    - für manuelle Formulare und Aktionen leicht integrierbar
    - Im Core bei allen relevanten Stellen integriert

### Neu

* Update Symfony-Komponenten (3.4.2), parsedown (1.6.4), whoops (2.1.14) (@gharlan)
* Backend-Übersetzungsdateien:
    - Aktualisierung Schwedisch (@interweave-media)
    - Aktualisierung Spanisch (@nandes2062)
    - Italienisch hinzugefügt (@Fanello, @lexplatt)
* Setup:
    - Warnung, wenn Setup nicht über HTTPS ausgeführt wird (@staabm)
    - Warnung, wenn Apache-Modul mod_security geladen ist (@staabm)
* Weniger strikte Default-Passwortregeln (nur min. 8 Zeichen) (@IngoWinter)
* Backend-Session wird duch regelmäßige Ajax-Calls erhalten (@IngoWinter)
* Bei Passwortfeldern kann in den Klartextmodus gewechselt werden (@pwechs83, @staabm, @tbaddade)
* Neue Consolen-Commands:
    - `cache:clear` (@bloep)
    - `package:install/uninstall/activate/deactivate` (@bloep)
    - `db:dump-schema` (@gharlan)
* In der Console ist die (Backend)Sprache fix auf englisch (@gharlan)
* In Log-Dateien werden Zeilenumbrüche erhalten (@VIEWSION)
* System-Log neu im Data- statt Cache-Ordner (@gharlan)
* `rex_response`: Neue Methode `preload()` zum Setzen von preload-Headern, wird für Font Awesome im Backend bereits genutzt (@bloep)
* `rex_request`: Neue Methode `isHttps()` (@staabm)
* `rex_socket`: 
    - Neue Methode `followRedirects()` (@gharlan)
    - Warnung wenn Non-SSL-Verbindung aufgebaut wird (@staabm)
* `rex_fragment`: Method-Chaining ist möglich (@DanielWeitenauer)

### Bugfixing

* Setup: 
    - Escaping fehlte an einigen Stellen (@staabm)
    - Teilweise kam es zu Fehlern während der Reinstallation der Addons (@gharlan)
* Profil: Beim EP `PROFILE_UPDATED` wurde die User-ID nicht korrekt übergeben (@gharlan)
* `rex_i18n`: Die Parameter wurden nicht escaped (@gharlan)
* `rex_form`: `setApplyUrl` hatte keine Auswirkung (@bloep)
* `rex_sql_table`: Bei noch nicht existenter Tabelle konnte es zu einer Exception kommen (@gharlan)


Version 5.4.0 – 04.10.2017
--------------------------

### Neu

* Updates: symfony/yaml (3.3.9), symfony/var-dumper (3.3.9), filp/whoops (2.1.10), erusev/parsedown (1.6.3)
* Neue Funktion `rex_escape`, diese kann und sollte statt `htmlspecialchars` für Ausgaben verwendet werden (@gharlan)
* Integration von symfony/console für die einfache Bereitstellung von Consolen-Kommandos in Addons (@gharlan)
* `rex_sql_table`: 
    - Tabellen können auch neu erstellt, umbenannt und gelöscht werden (@gharlan)
    - Spaltennamen und Spaltenreihenfolge kann geändert werden (@gharlan)
    - Indexes und Fremdschlüssel können verwaltet werden (@gharlan)
    - Es kann eine komplette Tabellendefinition angegeben werden und dann mit `ensure()` eine Überprüfung und ggf. Korrektur erreicht werden (praktisch für install.php in Addons) (@gharlan)
* `rex_sql`: Debug-Ausgaben werden über `dump`-Funktion ausgegeben (@alexplusde)
* Neue Klasse `rex_password_policy`, für das Backend können in der config.yml Passwortregeln hinterlegt werden (Achtung: Default gelten nun die Regeln min. 8 Zeichen, und jeweils min. 1 Kleinbuchstabe, Großbuchtsabe und Ziffer)
* Neue Extension Points: PROFILE_UPDATED, PASSWORD_UPDATED
* Backend-Sprachen:
    - English ergänzt (@ynamite)
    - Portugiesisch ergänzt (Taina Soares)
    - Spanisch ergänzt (@nandes2062)
* Session-Cookie-Parameter können (für Frontend und Backend getrennt) in config.yml gesetzt werden (default mit httponly und SameSite=strict) (@staabm)
* Eingeloggt-bleiben-Cookie als httponly (@staabm)
* Beim Logout werden die Daten im Browser zu der Website gelöscht (Privatsphäre) (@staabm)
* Bereits in den index.php-Dateien kann ein alternativer `path_provider` gesetzt werden für tiefgreifendere Pfadänderungen (@gharlan)
* Debug-Modus kann an der Body-Klasse `rex-is-debugmode` erkannt werden (@schuer)
* In der Tabelle rex_config liegt der Primary Key nun direkt auf (namespace, key), Spalte id entfällt (@gharlan)
* Bei Installation über git wird unter System bei der Version der Commit-Hash mit ausgegeben (@staabm)
* Whoops: Links zu php.net (@staabm)

### Bugfixes

* Setup:
    - Nach Auswahl "Datenbank existiert bereits" und "Update aus vorheriger Version" waren anschließend fälschlich wieder nur die Standardaddons aktiviert (@gharlan)
    - Beim Import eines vorhandenen Backups wurden nicht die Addons aus dem Backup aktiviert (@gharlan)
* Sprachdateien: 
    - Wenn ein Wert leer war, wurde die komplette folgende Zeile als Wert genommen (@gharlan)
    - Wenn ein Wert "=" enthielt, kam teilweise was falsches raus (@tyrant88)
    - Sprachkey für Schwedisch korrigiert (se_sv -> sv_se) (@gharlan)
* REX_VARs haben teilweise Warnungen geworfen in PHP 7.1 (@gharlan)
* `rex_list`: Funktionierte nicht mit MariaDB (@staabm)
* `rex_form`: Bei Container-Feldern wurden die Default-Werte ignoriert (@gharlan)
* `rex_select`: `countOptions()` lieferte teilweise falsches Ergebnis (@staabm)
* `rex_response`: Session locks in `sendFile()` werden vermieden (@staabm)
* `rex_clang`: Clang-ID wird einheitlich als `int` behandelt und zurückgegeben (@gharlan)
* `rex_sql`: Teilweise fehlte die Query in der Exception-Message (@gharlan)
* `rex_socket`: Die tatsächliche Ursache war bei Exceptions oft nicht ersichtlich (@gharlan)
* PJAX: Beim Absenden von Formularen wird nun nach oben gescrollt (@gharlan)
* Output Buffer wurden teilweise nicht korrekt beendet (@gharlan)
* System-Log: HTML in Log-Messages wurde nicht escaped (@gharlan)
* .htaccess in geschützten Ordnern: Anpassung für Apache 2.4 (@gharlan)
* Session-ID-Neugenerierung warf teilweise Warnungen (@gharlan)
* Im Chrome erschien beim Login nicht der Passwort-speichern-Dialog (@gharlan)


Version 5.3.0 – 14.02.2017
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
* Beim Aktivieren von Packages wurden nur deren Konflikte geprüft, aber nicht ob andere Packages Konflikte zu diesem notiert haben
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
