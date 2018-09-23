REDAXO-AddOn: project
=====================

Das project-AddOn dient als Basis AddOn für eigene projektspezifische Erweiterungen. Es wird nicht durch ein System Update aktualisiert. Daher bleiben alle dort abgelegten Dateien erhalten.

Installation
------------

Das AddOn wird mit dem REDAXO Core mitgeliefert und muss lediglich über die AddOn-Verwaltung installiert werden.

Verzeichnisse
-------------

Bei der Installation wird lediglich das Verzeichnis lib angelegt. Alle dort abgelegten Klassen stehen über den REDAXO Autoloader systemweit zur Verfügung und müssen nicht zusätzlich included werden.

Weitere Verzeichnisse (pages, fragments usw.) können direkt im Projekt AddOn angelegt werden. In der Dokumentation https://redaxo.org/doku/master/addon-struktur finden sich die entsprechenden Hinweise.
