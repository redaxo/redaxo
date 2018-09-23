REDAXO-AddOn: project
=====================

Das project-AddOn dient als Basis AddOn für eigene projektspezifische Erweiterungen. Es wird nicht durch ein System Update aktualisiert. Daher bleiben alle dort abgelegten Dateien erhalten.

Installation
------------

Das AddOn wird mit dem REDAXO Core mitgeliefert und muss lediglich über die AddOn-Verwaltung installiert werden.

Verzeichnisse
-------------

Bei der Installation wird lediglich das Verzeichnis lib angelegt. Alle dort abgelegten Klassen stehen über den REDAXO Autoloader systemweit zur Verfügung und müssen nicht zusätzlich included werden.

**Beispiel**

Im Verzeichnis `lib` legt man die Datei `my_helpers.php` an.

Der Code der Datei kann folgendermaßen aussehen:

```php
class my_helpers {
  public static function links_to_blank ($text) {
    return str_replace('<a href="http','<a target="_blank" href="http',$text);
  }
}
```

Nun kann man in jedem Modul, in dem man http(s) Links in einem neuen Fenster öffnen lassen will die Links ersetzen lassen:

`echo my_helpers::links_to_blank($text)`


Weitere Verzeichnisse (pages, fragments usw.) können direkt im Projekt AddOn angelegt werden. In der Dokumentation https://redaxo.org/doku/master/addon-struktur finden sich die entsprechenden Hinweise.

Dateien
-------

### boot.php

Die Datei boot.php wird bei der Initialisierung von REDAXO ausgeführt. Das heißt, dass der Code vor der Ausführung von Templates und Modulen ausgeführt wird.

So kann hier ein zusätzlicher Pfad für yform Templates angegeben werden

`rex_yform::addTemplatePath($this->getPath('yform-templates'));`

Nun werden Templates für die Ausgabe der yform Felder auch im Pfad `src/addons/project/yform-templates/[theme-name]` gesucht, wobei [theme-name] durch den Name des Themes (Standard ist bootstrap) ersetzt werden muss.

Mit diesem Code

```php
if (rex::isBackend()) {
    rex_view::addJsFile($this->getAssetsUrl('scripts/be_scripts.js'));    
}
```

wird im Backend von REDAXO die Datei `/assets/addons/project/scripts/be_scripts.js` geladen.

Auch der Einsatz von Extension-Points ist in der boot.php sinnvoll.

```php
if (!rex::isBackend()) {
    // eine Session im Frontend wird gestartet
    rex_login::startSession();    
    rex_extension::register('PACKAGES_INCLUDED', function() {
        // der Code hier wird erst ausgeführt, wenn alle AddOns geladen sind
        // ....
    });
}
```
