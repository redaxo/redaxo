
## Properties für Templates und Module

Hier können REDAXO-Properties gesetzt werden die man in Templates und Modulen verwenden kann.
Unter dem Menüpunkt **System** können die Properties verwaltet werden.

### Formatierung

Format: `propertyName = Inhalt`

* jede Property-Einstellung in einer Zeile
* ein Leerzeichen vor und nach dem Istgleich-Zeichen
* Kommentare beginnen mit '#' am Zeilenanfang
* Inline-Kommentare mit '#' möglich

> **Hinweis:**
Um keine bereits bestehenden REDAXO-Properties zu überschreiben empfiehlt es sich in der ersten Zeile einen Prefix zu setzen.
Beispiel: `PREFIX = my_`

Die Properties werden hier immer **ohne** den Prefix notiert z.B. `HalloText = Servus Welt!` und nur der Zugriff über `rex::getProperty` muss bei gesetztem `PREFIX = my_` **mit** dem Prefix erfolgen, also `rex::getProperty('my_HalloText');`.

### Verwendung im Template / Modul

```
// Zugriff auf Properties ohne gesetztem PREFIX
$value = rex::getProperty('HalloText');

// Zugriff auf Properties mit gesetztem PREFIX = my_
$value = rex::getProperty('my_HalloText');
```

## Anwendungs-Beispiel

Für ein Galerie-Modul müssen mindestens 3 Bilder ausgewählt werden.

**Property-Einstellungen**

```
PREFIX = meinProjekt_
beMinimumGalleryPics = 3
```

**Modul-Input**

```
<?php
// Hinweis im Edit-Modus (am Modul-Anfang)
$_imagelist = explode(',', "REX_MEDIALIST[1]");
if (rex_request('save', 'string', '') == '1') {
    if (count($_imagelist) < rex::getProperty('meinProjekt_beMinimumGalleryPics')) {
        echo rex_view::warning('Achtung! Keine oder zu wenige Bilder ausgewählt (mind. ' . rex::getProperty('meinProjekt_beMinimumGalleryPics') . ')! Es erfolgt keine Ausgabe!');
    }
}
?>

...
```

**Modul-Output**

```
<?php
// Hinweis nur im Backend (am Modul-Anfang), im Frontend keine Ausgabe
$_imagelist = explode(',', "REX_MEDIALIST[1]");
if (count($_imagelist) < rex::getProperty('meinProjekt_beMinimumGalleryPics')) {
    if (rex::isBackend()) {
        echo rex_view::warning('Achtung! Keine oder zu wenige Bilder ausgewählt (mind. ' . rex::getProperty('meinProjekt_beMinimumGalleryPics') . ')! Es erfolgt keine Ausgabe!');
    }
    return;
}
?>

...
```

## Empfehlungen

* PREFIX setzen!
* Properties im CamelCase notieren -> https://en.wikipedia.org/wiki/Camel_case
