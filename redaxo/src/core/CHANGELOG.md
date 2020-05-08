Changelog
=========

Version 5.10.1 – 08.05.2020
---------------------------

### Neu

* Update der externen Bibliotheken (u.a. jQuery 3.5.1)

### Bugfixes

* Es kam zu einem Fehler, wenn ein Addon keine `package.yml` oder darin keine `version` enthielt (@gharlan)
* Logout im Chrome war teils sehr langsam (@staabm)
* Accesskeys funktionierten nicht mehr (@bloep)
* Systembericht: Bei fehlerhafter zweiter Datenbankverbindung kam es zu der Ooops-Fehlerseite (@gharlan)
* `rex_sql_table`:
    - Spaltenreihenfolge wurde teils nicht korrekt gesetzt (@gharlan)
    - Bei mehrfachem Aufruf von `ensure` für eine Tabelle ohne Änderungen kam es zu einem Fehler (@gharlan)
* `rex_sql`: Bei einer Exception in `setDBQuery` wurde die DB-ID nicht auf die Ursprungs-ID zurückgesetzt (@staabm)
* `rex_file`: bei `copy` kam es zu einer Warnung, wenn man nicht der Fileowner der Datei ist (@gharlan)
* Command `user:create`: Die angelegten User konnten sich nicht einloggen (@staabm, @bloep)


Version 5.10.0 – 10.03.2020
---------------------------

### Security

* Markdown-Ausgaben (Readmes, Installer etc.) waren nicht geschützt gegen XSS (@gharlan)

### Neu

* Update der externen Bibliotheken (@gharlan)
* Setup: Beim erneuten Ausführen wird das vorhandene DB-Passwort nicht mehr angezeigt (@staabm)
* EOL-Warnungen für PHP/MySQL/MariaDB:
    - Analog zu PHP wird bei MySQL/MariaDB-Version gewarnt, die vom Hersteller nicht mehr gepflegt wird (@staabm)
    - Die EOL-Warnungen werden auch in der Console und im Systembericht ausgegeben (@bloep, @staabm)
* Datenbank:
    - SSL-Connections können verwendet werden (in `config.yml` konfigurierbar) (@staabm)
    - Es wird nun einheitlich die Collation `utf8_unicode_ci`, bzw. `utf8mb4_unicode_ci` (nicht mehr teils `*_general_ci`) (@gharlan)
* Addons können in der `package.yml` unter `default_config` die Default-Werte für `rex_config` hinterlegen (@gharlan)
* Neue Klasse `rex_version`:
    - Methode `isUnstable` zum Prüfen, ob eine Version eine Entwicklungsversion ("beta" etc.) ist (@staabm)
    - Weitere Methoden wurden in die Klasse verschoben (und die bisherigen als deprecated markiert) (@gharlan):
        - `rex_string::versionSplit` -> `rex_version::split`
        - `rex_string::versionCompare` -> `rex_version::compare`
        - `rex::getVersionHash` -> `rex_version::gitHash`
* `rex_string`: Neue Methode `sanitizeHtml`, um HTML aus unsicherer Quelle gegen XSS zu schützen (@gharlan)
* `rex_response`: Neue Methode `sendJson` (@staabm)
* `rex_file`:
    - Neue Methode `mimeType()` um den Mime-Type einer Datei zu bestimmen (liefert bessere Resultate als `mime_content_type()`, zum Beispiel für SVGs) (@gharlan)
    - Neue Methode `move` (@staabm)
* `rex_package/addon/plugin`: Neue Methode `require`, die wie `get` das Package-Objekt liefert, aber eine Exception wirft, wenn das Package nicht vorhanden ist (@gharlan)
* Console:
    - Es wird eine Warnung ausgegeben, wenn die Console mit einem anderen User ausgeführt wird als dem File-Owner von `/redaxo` (@skerbis, @bloep)
    - Neuer Command `package:delete` (@bloep)
* Versionsnummern werden überall im System (Addonverwaltung, Systembericht etc.) mit einem Icon markiert, wenn es Entwicklungsversionen sind ("beta" etc.) (@staabm)
* Systembericht als Markdown: Neuer Button "In die Zwischenablage kopieren" (@staabm)
* Speichern/Übernehmen-Buttons haben ein `title`-Attribut mit Erläuterungstext (@staabm)
* Auf der Lizenz-Page der Packages wird ein Link zu einer Seite mit Erklärungen zu den Lizenzen ausgegeben (@staabm)
* Beim Cache löschen wird auch der Opcache geleert (@gharlan)
* `php.ini`-Einstellung `html_errors` wird immer deaktiviert, um HTML-Markup in Whoops und im Log zu vermeiden (@gharlan)
* Code-Stabilität durch Tests und statische Code-Analyse verbessert (@staabm, @bloep, @gharlan)

### Bugfixes

* Bei tiefer verschachtelten Abhängigkeiten der AddOns wurde die Ladereihenfolge nicht immer korrekt entsprechend der Abhängigkeiten generiert (@gharlan)
* `rex_sql`: In der Debug-Ausgabe wurden in `fullquery` nicht immer die Parameter ersetzt (@gharlan)
* Es kam zu Fehlern, wenn Addons eine eigene (ältere) Version von Parsedown mitlieferten (@gharlan)
* Bei den `package:*`-Commands waren Addons, die gerade erst in den Addonordner gelegt wurden, nicht direkt verfügbar (@bloep)
* Die Tabelle `rex_config` hat seit einigen Versionen keine `id`-Spalte mehr, bei manchen war diese aber trotzdem noch vorhanden und führte zu Problemen beim Update (@gharlan)


Version 5.9.0 – 02.02.2020
--------------------------

### Neu

* Update der externen Bibliotheken (u.a. jQuery v3 und pjax v2) (@skerbis, @schuer, @gharlan)
* MySQL 8 wird unterstützt (@staabm, @gharlan)
* utf8mb4-Unterstützung (vollständiger Unicode-Zeichensatz inkl. Emojis): Kann über das Setup aktiviert werden (@gharlan)
* Neuer zentraler Ordner für Logdateien: `redaxo/data/log` (`rex_path::log()`; Pfad kann über Pathprovider geändert werden) (@gharlan)
* Setup:
    - Kann über den neuen Command `setup:run` auch in der Console durchgeführt werden (@bloep)
    - Sprachen sind nun alphabetisch sortiert (@tbaddade)
    - Warnung wenn "session.auto_start" aktiviert ist (@bloep)
    - Warnung vorbereitet für End-Of-Live von PHP 7.x ab Ende November 2022 (@staabm)
    - HTTPS-Option kann nur noch bei Aufruf über HTTPS gesetzt werden, damit man sich nicht selbst aus dem Backend ausschließen kann (@bloep)
    - HSTS kann nicht mehr über das Setup (nur direkt über config.yml) gesetzt werden (@bloep)
    - Beim DB-Host kann der Port mit angegeben werden ("localhost:3306") (@staabm, @gharlan)
    - DB-Name kommt erst nach Host/Benutzer/Passwort (@gharlan)
    - Default-DB-Name nun "redaxo5" statt "redaxo_5_0" (@gharlan)
    - DB-Host/Benutzer/Name werden getrimmt (@aeberhard)
    - Es wird `rex_sql_table` verwendet für bessere Teilkorrekturen der DB (@tbaddade)
* In der config.yml kann über `editor_basepath` der Basispfad für die Editor-URLs geändert werden (nützlich für Docker) (@bloep)
* AddOn-Verwaltung: Suchfeld für AddOns (@danspringer)
* Systemlog:
    - 100 statt 30 Zeilen (@aeberhard)
    - Button zum Download der Datei (@aeberhard)
* Markdown-Pages (Readme): Die Sprungnavi ist nun rechts angeordnet (@schuer)
* Layout der Credits-Page optimiert und an Addonverwaltung angeglichen (@schuer)
* Whoops: REDAXO-Logo ist mit Startseite verlinkt (@gharlan)
* REX_VARs: Callbacks bekommen den Variablennamen und die zugehörige Klasse als Parameter `var` und `class` übergeben (@gharlan)
* `rex_sql`:
    - Über `getDbType()` kann der Type (MySQL oder MariaDB) abgefragt werden, über `getDbVersion()` die normalisierte Version (@gharlan)
    - Mit `escapeLikeWildcards()` können "%" und "_" escaped werden für `LIKE`-Ausdrücke (@gharlan)
* `rex_sql_table`:
    - DB-ID kann übergeben werden, somit auch nutzbar für die weiteren DBs (@thorol, @gharlan)
    - Bei `ensureGlobalColumns()` kann über den ersten Parameter die Position der Spalten festgelegt werden (@tbaddade)
* `rex_sql_schema_dumper`: Bei entsprechener Spalten-Kombi wird Shortcut `ensureGlobalColumns` genutzt (@gharlan)
* Fragment `core/page/section`: Attribute können übergeben werden (@tbaddade)
* Console-Commands:
    - Neuer Command `config:set` um Werte in der `config.yml` zu setzen (@bloep)
    - `db:set-connection` prüft nun, ob die neue Verbindung valide ist (kann per `--force` deaktiviert werden) (@bloep)
* Aktualisierung Backend-Übersetzungdateien: Schwedisch (@interweave-media), Spanisch (@nandes2062), Englisch (@ynamite)
* Englische Übersetzung der Readme des project-Addons (@skerbis)
* ETag-Header wird in Safari nicht mehr deaktiviert, da der Safari-Bug nicht mehr zu bestehen scheint (@gharlan)
* Der htaccess-Check-Cookie heißt nun `rex_htaccess_check` statt `htaccess_check` (@alexplusde)
* Code-Stabilität durch statische Code-Analyse verbessert (@staabm)

### Bugfixes

* `rex_sql`: Bei `->setWhere(['name' => 'a'])->setValue('name', 'b')` wurde fälschlich der Wert aus WHERE auch für SET verwendet (@gharlan)
* `rex_sql_table`: Beim Setzen von Primary Keys für Tabellen, die bisher keinen hatten, kam es zu einem Fehler (@gharlan)
* `rex_list` warf mit PHP 7.4 Notices "Trying to access array offset on value of type null" (@gharlan)
* `rex_socket`: Es kam teilweise zur Warnung "Undefined variable: errno" (@staabm)
* `rex_config`: Wenn während eines Requests `removeNamespace()` und danach `set()` für den selben Namespace aufgerufen wurde, kam es zu einem Fehler (@bloep)
* `rex_api_function`: Statische Methode `hasMessage` warf einen Fehler, wenn keine Api-Func aufgerufen wurde (@gharlan)
* `rex_log_file`: Pipe-Zeichen "|" konnte nicht in der Log-Message verwendet werden (@gharlan)
* Console-Commands:
    - `user:create` warf einen Fehler (@bloep)
    - `db:set-connection` konnte nur verwendet werden, wenn schon eine gültige DB-Verbindung hinterlegt war (@bloep)
    - `db:set-connection` hat fälschlich für nicht gesetzte Optionen deren Wert mit `null` gesetzt (@bloep)
    - `package:install`: Installation von Plugins von nicht aktivierten Addons wurde nicht unterbunden (@bloep)
* Im Setup bei "Aktualisierung der Datenbank" waren nicht alle Klassen dem Autoloader bekannt während der Re-Installation der Addons (@gharlan)
* Identität wechseln: Beim Zurückwechseln kommt es nicht mehr zu einem Fehler, wenn schon in einem anderen Tab zurückgewechselt wurde (@tbaddade)
* Addonverwaltung: Beim Öffnen der Hilfe/Lizenz eines Addons wird korrekt nach oben gesprungen (@gharlan)
* Bei Session-Start-Fehlern wurde der spezifische Grund unterschlagen (@gharlan)
* Datumsformat sprachspezifisch vereinheitlicht/korrigiert (@gharlan)


Version 5.8.1 – 01.11.2019
--------------------------

### Neu

* Update der externen Bibliotheken

### Bugfixes

* Whoops-Seite: Safemode-Button wieder sichtbar (@bloep)
* Benutzerwechsel: beim Zurückwechseln in zwei Browserfenstern kam es unnötigerweise zu einer Exception (@tbaddade)
* Unter System wurde an die REDAXO-Version teils fälschlich der Projekt-Git-Hash angehangen (@gharlan)
* `rex_form`: Beim Löschen von Datensätzen wurden die Prios nicht neu gesetzt (@dpf-dd)
* `rex_sql`: Aufruf `insertOrUpdate` ohne tatsächliche Änderungen führte fälschlich zu einer Exception (@pschuchmann)
* `rex_sql_table`: Fehlermeldung bei Kombi `setName` und `alter` für nicht-existente Tabelle korrigiert (@gharlan)


Version 5.8.0 – 20.08.2019
--------------------------

### Neu

* PHP-Mindestversion angehoben auf 7.1.3
* Update der externen Bibliotheken (u.a. Symfony components 4.3)
* Wenn Debug-Mode aktiv, wird das Frontend vor Crawlern versteckt (noindex) (@staabm)
* Vor Aktivierung des Debug-Modes kommt eine Bestätigungsbox (@skerbis)
* Session-Cookie:
    - `samesite` default auf `lax` statt `strict`, um unerwartete Backend-Logouts zu vermeiden (@staabm)
    - `samesite` kann neu auch auf `none` gesetzt werden (@staabm)
* `rex_form`: Statt der Konstante `REX_FORM_ERROR_VIOLATE_UNIQUE_KEY` (deprecated) ist nun `rex_form::ERROR_VIOLATE_UNIQUE_KEY` zu verwenden (@staabm)
* Beispiel-`.gitignore` wird mitgeliefert (@schuer)
* Aktualisierung Backend-Übersetzungdateien: Schwedisch (@interweave-media), Spanisch (@nandes2062), Englisch (@tyrant88)

### Bugfixes

* `rex_form`: Wenn ein Fieldsetname mit "?" endete, wurden die Werte nicht gespeichert (@gharlan)
* `rex_config_form`: Es konnten nicht zwei Formulare auf einer Seite verwendet werden (@gharlan)
* `rex_stream`: Warning in PHP 7.4 vermeiden (@gharlan)
* Command `config:get`: Ausgabe endete nicht mit einer Newline (@gharlan)
* Textkorrekturen und -vereinheitlichungen (@marcohanke, @sebastiannoell)
* Im Setup stand im Header unnötigerweise "Nicht angemeldet" (@gharlan)


Version 5.7.1 – 01.04.2019
--------------------------

### Bugfixes

* REDAXO 5.7.x kann nur ausgehend von >=5.6 aktualisiert werden, geprüft wurde aber nur auf >= 5.4 (@gharlan)
* Asset-Streaming (über `redaxo/index.php`):
    - `?asset=`-Parameter unterstützte keine absoluten Pfade (`/assets/...`), was zu Problemen in manchen AddOn-Konstellationen führen konnte (@staabm)
    - SourceMap-Dateien wurden teils versucht über falsche Pfade zu laden (werden nun gar nicht mehr geladen) (@gharlan)
* `rex_config`: Wenn ein Wert gesetzt, und der Key direkt wieder gelöscht wurde, kam es zu einem Fehler (@gharlan)
* Die Benutzerrechte wurden case-sensitive sortiert (@gharlan)
* Update der externen Bibliotheken (@gharlan)


Version 5.7.0 – 12.03.2019
--------------------------

### Wichtig

REDAXO 5.7.x ist die letzte Version die mit PHP 7.0 oder älter kompatibel ist.
Ab REDAXO 5.8.x wird PHP 7.1 oder neuer vorrausgesetzt.

### Neu

* System-Page:
    - Überarbeitung/Optimierung von System/Einstellungen (@tbaddade, @skerbis)
    - Zentrale Page für Logdateien, mit REDAXO-, PHP-, PHPMailer-Log und zukünftig ggf. weiteren (@staabm)
    - Packages können eigene Logfiles in der neuen zentralen System-Log-Seite einbinden (@staabm)
    - Systembericht mit Infos zu REDAXO, AddOns, PHP, Server (auch als Markdown zum Kopieren und Verwenden in GitHub-Issues etc.) (@gharlan)
* Fehlerbehandlung:
    - Whoops: Button "Copy as markdown" um Exception, Stacktrace und Systembericht zusammen als Markdown zu erhalten für Issues etc. (@gharlan)
    - Schönere Fehlerseite im Frontend und Backend (wenn nicht als Admin eingeloggt) (@elricco, @staabm, @tbaddade)
    - Die neuen Fehlerseiten können via Fragment angepasst werden (@tbaddade, @staabm)
* Editor-Integration:
    - Unter System kann ein Editor ausgewählt werden; Quellcode-Dateien werden dann (z.B. in Whoops) so verlinkt, dass man sie direkt in dem Editor öffnen kann (@staabm, @gharlan)
    - Mit der `rex_editor`-Klasse können an weiteren Stellen Editor-URLs erzeugt werden (@staabm)
    - Über den EP `EDITOR_URL` können die URLs manipuliert werden (@gharlan)
* Console:
    - Manche Core Commands können nun bereits vor dem Setup ausgeführt werden (@bloep)
    - Neuer Command `config:get` (@bloep)
    - Neuer Command `db:set-connection` (@bloep)
    - Neuer Command `user:create` (@staabm)
* `rex`: Neue Methode `isFrontend` (@staabm)
* `rex_list`/`rex_form`: Nutzbar mit den weiteren Datenbanken (@gharlan)
* `rex_i18n`: Neue Methode `msgInLocale` zum Übersetzen in andere Sprachen ohne die Default-Sprache zu ändern (@staabm)
* `rex_path`: Neue Methode `relative()` um aus einem absoluten Pfad einen relativ zum Projekt-Root zu bekommen (@gharlan)
* `rex_file`: Schreibvorgänge sind nun atomar (@staabm)
* `rex_sql`:
    - `addGlobal[Create/Update]Fields`: Umgebung (frontend/console) als Defaultwert für Benutzer (@staabm)
    - Debug-Ausgabe erweitert um aufgelöstes SQL-Statement inkl. Parametern (@aeberhard)
* `rex_clang`: Methode `count` hat optionalen Parameter `$ignoreOffline` (@tbaddade)
* `rex_response`: Unterstützung für HTTP-Range (@bloep)
* `rex_view`: Für JS-Dateien können Optionen gesetzt werden (defer/async/immutable) (@staabm)
* Es werden unterschiedliche Namespaces für Session-Variablen im Frontend und Backend verwendet, über `rex_request::clearSession` können diese getrennt voneinander gelöscht werden (@staabm)
* Neue Api-Function `rex_api_has_user_session` um den Status der Backend-Session abzufragen. Damit können u.a. Single-Sign-On Mechanismen realisiert werden. (@staabm)
* Setup-Hinweise bzgl. Sicherheit:
    - Warnung bei veralteter PHP-Version (@staabm)
    - Warnung bei XX7-Berechtigungen im Dateisystem (@staabm)
* README-Ausgabe, Markdown-Pages:
    - Sprachunterstützung (README.de.md etc.) (@staabm, @gharlan)
    - Sprungankernavi (@gharlan, @tbaddade)
* Readme für das Project-AddOn (@dtpop)
* Aktiver Debug-Modus wird durch Icon im Header angezeigt (@schuer)
* Verständlichere CSRF-Meldung (@alexplusde)
* Backend-Übersetzungdateien:
    - Neu: Niederländisch (noch ohne Core-AddOns) (@MaxKorlaar)
    - Aktualisierung: Englisch (@ynamite, @skerbis), Schwedisch (@interweave-media), Spanisch (@nandes2062)
* Default-Passwortregeln: Max. Länge von 4096 Zeichen (@staabm)
* bootstrap-select wird an weiteren Stellen verwendet (@skerbis, @schuer)
* REX-Vars: Generierter PHP-Code enthält am Anfang Original-Var-Code als Kommentar (@staabm, @gharlan)
* Verbesserung der Usability durch neue Beschreibungstexte, oder Präzisierung vorhandener (@schuer, @alexplusde)
* Datum aus Footer entfernt (@staabm)
* htaccess-Check: Bei den Direktaufrufeversuchen wird ein Parameter `?redaxo-security-self-test` an die Dateien gehangen (@staabm)
* Sicherheit:
    - Bei Logout aus dem Backend werden temporäre Daten auf dem Server sofort gelöscht (@staabm)
    - Im Backend wird eine rudimentäre HTTP Content-Security-Policy verwendet (@staabm)
* Performance:
    - Backend-Assets können optional über index.php geladen werden, um optimierte Cache-Header (immutable) setzen zu können (aktiv für Core-Assets) (@staabm)
    - Per Server Timing Api werden im Debug-Modus, oder bei authentifizierten Adminsessions, Metriken an den Client gesendet (@staabm)
    - Weniger Dateioperationen im Backend um Datei-basiertes Cachen zu beschleunigen (@staabm)
    - Übersetzungen können schneller verarbeitet/dargestellt werden (@staabm)
    - Viele kleinere und größere Performance-Optimierungen (@staabm)
* Update der externen Bibliotheken
* API-Dokumentation unter https://www.redaxo.org/api/master/ übersichtlicher durch neue subpackages (@staabm)

### Bugfixes

* Versionsbedingungen: Bei `^2.0` wurde fälschlich `3.0-beta` akzeptiert (@gharlan)
* Profil: Sprachen waren nicht sortiert und Änderungen wirkten sich nicht direkt nach Speichern aus, erst nach Reload (@skerbis, @bloep)
* Bei aktiviertem Safe-Mode blieb der Button unter "System" trotzdem bei "Safe mode aktivieren" (@skerbis)
* `rex_sql`: Insert ohne explizite Values warf Fehler (@gharlan)
* `rex_sql_table`: Für `timestamp`/`datetime`-Spalten konnte nicht der Default-Wert `CURRENT_TIMESTAMP` gesetzt werden (@gharlan)
* `rex_form`: Media-/Link-/Prio-Felder konnten nicht mit `rex_form_base` bzw. `rex_config_form` verwendet werden (@christophboecker)
* `rex::getVersionHash` funktionierte nicht auf Windows Servern (@staabm)
* Autoloader-Cache wird bei Fehlern nicht mehr geschrieben, um unvollständigen Cache zu vermeiden (@staabm)
* Nach Session-Ablauf wird bei erneutem Seitenaufruf der Browser-Cache gelöscht (wie bereits bei explizitem Logout) (@staabm)
* Besseres Escaping nutzen mittels `rex_escape` (@bloep, @gharlan)
* EP `PASSWORD_UPDATED`: User-ID wurde nicht korrekt übergeben (@staabm)
* Im Chrome kam es zu Warnungen bzgl. des Font-Preloadings (@bloep)
* Wenn der Client keinen `User-Agent`-Header schickt, kam es zu einer Warnung (@staabm)
* Bei frühen Fehlern in der Console konnte es passieren, dass die HTML-Fehlerseite ausgegeben wurde (@staabm)


Version 5.6.5 – 10.12.2018
--------------------------

### Security

* Update des phpmailers wg. Sicherheitslücken

### Bugfixes

* Update der externen Bibliotheken


Version 5.6.4 – 01.10.2018
--------------------------

### Security

* Sicherheitslücken (SQL-Injection) in der Benutzerverwaltung geschlossen (gemeldet von @Balis0ng, ADLab of VenusTech) (@staabm)
* XSS Sicherheitslücken (Cross-Site-Scripting) im Medienpool behoben (gemeldet von @Balis0ng, ADLab of VenusTech) (@bloep)
* XSS Sicherheitslücken (Cross-Site-Scripting) im Mediamanager behoben (gemeldet von @Balis0ng, ADLab of VenusTech) (@staabm)


Version 5.6.3 – 26.09.2018
--------------------------

### Security

* Kritische Sicherheitslücke (SQL-Injection) in der rex_list Klasse geschlossen (gemeldet von @Balis0ng, ADLab of VenusTech) (@staabm)


Version 5.6.2 – 10.07.2018
--------------------------

### Security

* Kritische Sicherheitslücke (Path Traversal) im Media-Manager-Addon geschlossen (gemeldet von Matthias Niedung, https://hackerwerkstatt.com) (@gharlan)

### Bugfixes

* `rex_sql`: BC-Break in `showTables` rückgängig gemacht, die Methode liefert nun auch wieder Views; Methode als deprecated markiert, stattdessen neue nicht-statische Methoden `getTables`, `getViews` und `getTablesAndViews` (@gharlan)
* Command `db:connection-options` konnte nicht mit `mysqldump` genutzt werden (@gharlan)
* `rex_http_exception`: Message der Orignal-Message nutzen statt leerer Message (@gharlan)


Version 5.6.1 – 21.06.2018
--------------------------

### Neu

* Erläuterung für Debug-Modus (@alexplusde; Übersetzung: @ynamite, @interweave-media, @nandes2062)
* Performance-Optimierungen (@staabm)

### Bugfixes

* Identität wechseln: Beim Versuch, in einen inaktiven Benutzer zu wechseln, kam es zu einem Fehler (@gharlan)
* `rex_delete_cache`: Wenn vor dem Aufruf `rex_config`-Werte gesetzt wurden, gingen diese verloren und wurden nicht gespeichert (@gharlan)
* `rex_list`: Bei Nutzung mehrere Listen auf einer Page griff die Paginierung immer synchron für beide Listen (@staabm)
* `rex_sql`: Bei Aufruf von `next()` und anschließend `getRow($fetch_type)` hatte der Parameter `$fetch_type` keine Auswirkung (@joachimdoerr)
* `rex_fragment::parse`: Nicht funktionierenden Parameter `$delete_whitespaces` entfernt (@staabm)


Version 5.6.0 – 05.06.2018
--------------------------

### Security

* Siehe mediapool-Changelog
* `rex_string::buildAttributes`: Die Attribute wurden nicht escaped, wodurch unter Umständen XSS möglich war (ggf. kontrollieren, ob man dort Attribute übergeben hat, die bereits escaped waren) (@gharlan)

### Neu

* MySQL-Mindestversion 5.5.3
* Update Symfony-Komponenten (3.4.11), Symfony-Polyfills (neu: ctype) (1.8.0), parsedown (1.7.1) (@gharlan)
* HTTPS kann für Frontend und/oder Backend erzwungen werden (Umleitung und optional HSTS-Header) über `config.yml` und Setup (@bloep)
* Admins können in die anderen Benutzer wechseln, ohne deren Passwort zu kennen (@gharlan)
* Im Debug-Mode kann Whoops optional auch für Warnings/Notices aktiviert werden (@gharlan)
* Safe-Mode kann aus System-Page heraus gestartet werden (@alexplusde, @tbaddade)
* Setup:
    - Webserver-Adresse wird automatisch eingetragen (@alexplusde, @tbaddade)
    - bootstrap-select wird verwendet (@skerbis)
* Packages-Page: Lizenz in Kurzform wird gelistet mit Link zu kompletter Lizenz (@staabm, @tbaddade, @gharlan)
* project-Addon wird per default spät geladen (`load: late` in package.yml, wird bei Update nicht gesetzt) (@gharlan)
* Beim Core-Update werden in der `config.yml` alle neuen Optionen ergänzt (@gharlan)
* Doku wird im Footer verlinkt (@olien)
* Backend-Übersetzungsdateien:
    - Englisch und Deutsch dienen als Fallback, wenn Übersetzungen fehlen (@gharlan)
    - Englisch aktualisiert (@ynamite)
    - Schwedisch aktualisiert (@interweave-media)
    - Spanisch aktualisiert (@nandes2062)
* Neue Consolen-Commands:
    - `user:set-password`: Neues Passwort für Benutzer setzen (@gharlan)
    - `setup:check`: Umgebung (Versionen, Dateirechte...) prüfen (@staabm)
    - `assets:sync`: Assets zwischen /assets und den assets-Ordnern in `src` synchronisieren (@staabm)
    - `db:connection-options`: Liefert die Optionen um sich mit dem `mysql` cli tool mit der DB zu verbinden (@gharlan)
* `rex_form`:
    - Neue abstrakte Basisklasse `rex_form_base` für alternative Speichermethoden, neue Klasse `rex_config_form` für Speicherung in `rex_config` (@gharlan)
    - Führende/nachfolgende Leerzeichen werden nach dem Senden entfernt (@staabm)
* `rex_sql`:
    - Für die Connection wird utf8mb4 genutzt (@gharlan)
    - Neue Methoden für die Nutzung von Transactions (@staabm)
    - Neue Methode `insertOrUpdate` für `INSERT .. ON DUPLICATE KEY UPDATE`-Queries (@gharlan)
    - Mehrere Datensätze können gleichzeitig eingefügt/aktualisiert/ersetzt werden (`addRecord`) (@gharlan)
* `rex_backend_login::hasSession`: Es wird nun keine Session mehr gestartet, wenn bereits der Session-Cookie nicht existiert (@VIEWSION)
* `rex_response`:
    - `sendFile`: Dateiname kann angegeben werden (@bloep)
    - `sendResource`: Content-Disposition und Dateiname können angegeben werden (@gharlan)
    - Neue Methode `sendCookie` (@staabm)
* `rex_file::copy`: Zugriffszeit wird auch übernommen (@staabm)

### Bugfixes

* Nach explizitem Logout funktioniert teils der direkte erneute Login nicht (CSRF-Token-Fehler) (@bloep)
* Cache löschen: Teils blieb der `rex_config` Cache erhalten (@gharlan, @tbaddade)
* Beim Deaktivieren/deinstallieren von Packages wurde dessen Cache nicht gelöscht (@bloep)
* `rex_sql`: `showTables` enthielt auch Views (@gharlan)
* `rex_sql_table`: Es konnten nicht mehrere Fulltext-Indexe gesetzt werden (@gharlan)


Version 5.5.1 – 05.01.2018
--------------------------

### Security

* Kritische Sicherheitslücke (Path Traversal) im Media-Manager-Addon geschlossen (gemeldet von @patrickhafner, KNOX-IT GmbH) (@gharlan)

### Bugfixes

- `rex_sql::hasNext` hat teilweise fälschlich `true` geliefert (@DanielWeitenauer)
- `rex_console_command::decodeMessage` hat Anführungszeichen nicht korrekt behandelt (@staabm)


Version 5.5.0 – 21.12.2017
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
* bootstrap-select wird an mehr Stellen verwendet (statt normale Selects) (@skerbis)
* `rex_response`: Neue Methode `preload()` zum Setzen von preload-Headern (@bloep)
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
