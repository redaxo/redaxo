Changelog
=========

Version 2.8.0 – XX.08.2019
--------------------------

* Customizer-Farbe wird für `theme-color`-Metatag verwendet (@tbaddade)
* Assets nutzen immutable cache (@staabm)
* ID- und Prio-Spalten breiter (für größere Zahlen) (@tbaddade)
* Abstand nach Paginierung korrigiert (@tbaddade)
* SCSS-Compiler: Methode `setStripComments` entfernt, da diese sowieso noch nie funktioniert hat (@staabm)


Version 2.7.1 – 01.04.2019
--------------------------

* Markdown-Ausgabe: Layout nicht mehr in der Breite zerschießen (@ansichtsache)


Version 2.7.0 – 12.03.2019
--------------------------

* Layout für neue Core-Komponenten und diverse kleine Optimierungen (@tbaddade)
* Hauptnavi: Weniger Padding (top/bottom) (@schuer)
* Neue Favicons (@schuer)
* Consolen-Command `be_style:compile` (@bloep)
* Codemirror-Integration verbessert (@aeberhard)
    - Sourcen verkleinert (@aeberhard, @staabm)
    - Ergänzt um Suche (@aeberhard)
    - Vereinfachte Einbindung systemweit (@aeberhard)
    - CodeMirror wird nur geladen wenn er auch benötigt wird (@aeberhard, @staabm)
* Customizer: 
    - Bessere Default-Erkennungsfarbe (@skerbis)
    - Bessere Darstellung des Links zur Website im Header (@schuer)


Version 2.6.1 – 10.07.2018
--------------------------

* Keine fixe Breite für die Aktionsspalten in Tabellen (@gharlan)
* Normale Schriftgröße für `<blockquote>` (@gharlan)


Version 2.6.0 – 05.06.2018
--------------------------

* Update bootstrap-select (1.12.4), scssphp (0.7.6) (@gharlan)
* Update CodeMirror (5.38) mit neuen Optionen (@aeberhard)
* Korrektur Suchfeld in bootstrap-select (@skerbis)


Version 2.4.0 – 21.12.2017
--------------------------

* Font-Awesome wird per preload-Header vorgeladen (@bloep)


Version 2.3.0 – 04.10.2017
--------------------------

* `max-width` für iframe, img, svg, video, object und embed in Slices im Backend (@skerbis)
* customizer: Bei der Erkennungsfarbe können nun auch Farbangaben wie `rgba(...)` genutzt werden (@gharlan)


Version 2.2.1 – 17.02.2017
--------------------------

* In Version 2.2.0 wurden die Styles aus be_style und be_style/redaxo nicht mehr als erstes geladen


Version 2.2.0 – 14.02.2017
--------------------------

* Update bootstrap 3.3.7
* Update fontawesome 4.7.0
* Update bootstrap-select 1.12.1
* REDAXO-Logo auf Loginseite sprang teilweise
* Optimierung Header auf Mobilgeräten
* Optimierung Bootstrap-Modals
* SCSS-Compiler: Ordner werden angelegt, wenn noch nicht vorhanden
* Customizer: Neues Theme material


Version 2.1.1 – 15.07.2016
--------------------------

* Update fontawesome 4.6.3
* Update scssphp 0.6.5
* Update bootstrap-select 1.10.0
* Diverse optische Korrekturen
* Customizer: Icon von "Link zur Website" auch klickbar (@alexplusde), Codemirror-Fullscreen-Modus gefixt (@alexplusde)
