Cronjob-Addon
=============

Dieses Addon ermöglicht es, Cronjobs in einem jeweils festgelegten Intervall ausführen zu lassen. Es gibt drei Standardtypen: `PHP-Code`, `PHP-Callback` und `URL-Aufruf`.

Addons koennen weitere spezielle Typen zur Verfügung stellen (Beispiel: Backup-Addon für automatische Datenbank-Sicherungen).

Es stehen drei Ausführungsumgebungen zur Auswahl, zu denen die Cronjobs zugeordnet werden können: `Frontend`, `Backend` und `Skript`.

Bei `Frontend` und `Backend` wird der Job jeweils beim nächsten Seitenaufruf nach Erreichen des nächsten Ausführungszeitpunkts ausgeführt. Zusätzlich kann noch ausgewählt werden, ob der Job am Skriptanfang oder Skriptende ausgeführt werden soll. Bei Skriptende wird er erst nach Auslieferung der Antwort an den Client ausgeführt, erzeugt somit keine Zeitverzögerung für den Benutzer.

Die Nutzung der Umgebung `Skript` muss ein echter Cronjob auf das Skript `redaxo/src/addons/cronjob/bin/run` gelegt werden, mit einem genügend kleinen Zeitinterval. Das Cronjob-Addon prüft weiterhin selbst, welche Jobs tatsächlich an der Reihe sind, und führt nur diese aus.

Artikel-Status Cronjob
----------------------

Dieser Cronjob-Typ schaltet Artikel automatisch online (bzw. offline), wenn das in den Meta Infos eingestellte "Online von"-Datum (bzw. "Online bis") erreicht wurde. Diese Felder müssen über die Meta Infos bei einem Artikel ergänzt werden. Die Felder heißen `art_online_from` und `art_online_to`.

Tabellen-Optimierung Cronjob
------------------------------

Dieser Cronjob-Typ reorganisiert und optimiert alle rex_* Tabellen in der Datenbank.  `OPTIMIZE TABLE ` wird ausgeführt. Besonders für Tabellen mit vielen INSERT, UPDATE oder DELETE Statements ist OPTIMIZE vorgesehen. Die Tabellen und ihre Indices werden wieder neu strukturiert.
