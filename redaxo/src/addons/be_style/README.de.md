be_style-Addon
=============

Dieses Addon stellt die Styles, Skripte und Grafiken für das REDAXO-Backend bereit.

## Styles kompilieren

Bei Änderungen müssen die Styles neu kompiliert werden. Hierfür stellt **be_style** mehrere Varianten zur Verfügung.

### 1. via REDAXO Konsole
Über die Konsole von REDAXO können die Styles neu kompiliert werden. Hierfür benutzt man den von **be_style** bereitgestellten Kompilierungsvorgang via `be_style:compile` oder `styles:compile`.

### 2. via package.yml
In der `package.yml` von dem PlugIn **redaxo** kann der Wert von `compile` von `0` auf `1` gesetzt werden.
Beim nächsten Aufruf als eingeloggter Backend Benutzer werden die Styles neu kompiliert.

##### Hinweis
Der Wert sollte im Anschluss wieder auf `0` gesetzt werden, da sonst bei jedem Seitenaufruf die Styles neu kompiliert werden.


# Extension Points

### BE_STYLE_SCSS_FILES
Über diesen Extension Point können weiere SCSS-Dateien zum Kompiliervorgang von REDAXO hinzugefügt werden.
Dies bietet sich an, wenn Variablen oder CSS-Eigenschaften überschrieben werden sollen.

##### Beispiel
```php
rex_extension::register('BE_STYLE_SCSS_FILES', function(rex_extension_point $ep) {
   $files = $ep->getSubject();
   array_unshift($files, '/pfad/zu/meiner/scss-datei');
   return $files;
});
```

### BE_STYLE_SCSS_COMPILE
Über diesen Extension Point können eigene CSS-Dateien erstellt werden. Bei der Verwendung eigener Styles beispielsweise im AddOn oder im Frontend können über diesen EP komplett getrennte CSS-Dateien erstellt werden.

##### Beispiel
```php
rex_extension::register('BE_STYLE_SCSS_FILES', function(rex_extension_point $ep) {
   $files = $ep->getSubject();
   $files[] = [
       'scss_files' => 'pfad/zu/scss/dateien',   # Quell SCSS Dateien als string oder array
       'css_file' => 'pfad/zur/ziel/css/datei',  # Pfad zum Speicherort, wo die CSS Datei abgelegt werden soll

       'copy_dest' => 'pfad/zur/kopie',          # Optional: Wenn die Datei an einem zweiten Ort z.B. dem assets ordner abgelegt werden soll, kann dies hier angegeben werden 
   ];
   return $files;
});
```
