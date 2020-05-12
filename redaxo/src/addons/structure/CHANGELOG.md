Changelog
=========

Version 2.10.1 – 08.05.2020
---------------------------

### Bugfixes

* Bei Fehlern während der Artikelcache-Generierung wurde im Frontend eine Fehlermeldung ausgegeben, die den vollen Cachepfad enthielt (@gharlan)
* Beim Backendaufruf von nicht existenten Artikeln erschien keine Fehlermeldung (@tbaddade)
* Es wurden teilweise falsche Übersetzungsschlüssel verwendet (@bloep)


Version 2.10.0 – 10.03.2020
---------------------------

### Neu

* Slice-Status (online/offline) kann gesetzt werden (Übernahme von bloecks/status) (@gharlan, @schuer)
* `REX_TEMPLATE_KEY`-Platzhalter für Templates/Module hinzugefügt (@staabm)
* Modulen können (analog zu den Templates) eindeutige Keys vergeben werden (inkl. `REX_MODULE_KEY`-Platzhalter) (@alexplusde, @staabm)
* Der Status-Schalter nutzt ein Dropdown, wenn weitere Status hinzugefügt wurden (Bsp. accessdenied) (@alexplusde)
* Template/Module löschen: Auflistung der Artikel verschönert, in denen es noch verwendet wird, und es werden die Artikel in allen betroffenen Sprachversionen aufgelistet (@gharlan)
* Spalten in `rex_article_slice`-Tabelle umsortiert (`article_id` und `module_id` weiter nach vorne) (@gharlan)

### Bugfixes:

* Einfache Rex-Vars wie `REX_MODULE_ID`/`REX_SLICE_ID` wurden erst nach den richtigen Rex-Vars wie `REX_VALUE[X]` ersetzt, dadurch konnten sie nicht nicht als Argumente innerhalb der Vars genutzt werden und wurden auch im eigentlichen Inhalt der Values ersetzt (@gharlan)
* Nach dem Speichern von Blöcken erschien die Erfolgsmeldung nicht mehr im Block (@gharlan)
* `rex_category`/`rex_article`: Methoden wie `getId`, `getParentId` etc. lieferten die Zahl als String statt als Integer (@gharlan)


Version 2.9.0 – 02.02.2020
--------------------------

### Neu

* Neue Rechte `addCategory[]`, `editCategory[]`, `deleteCategory[]`, `addArticle[]`, `editArticle[]`, `deleteArticle[]` (@gharlan)
* Templates können eindeutige Keys vergeben werden und dann darüber (statt über die ID) eingebunden werden (`REX_TEMPLATE[key=my_key]`) (@tbaddade)
* Toggle-Status der Panels in der Sidebar (Metainfos etc.) wird per Localstorage gespeichert (@IngoWinter)
* `rex_navigation`:
    - Die Callbacks erhalten als weiteren Referenzparameter den Linktext und können ihn darüber ändern (@alexplusde)
    - Markup kann über Klassenerweiterung und Überschreiben der neu dafür vorgesehenen Methoden angepasst werden (@DanielWeitenauer, @gharlan)
* Neue Klasse `rex_template_select` für die Template-Auswahl (@DanielWeitenauer)
* Neue Methode `rex_content_service::addSlice` (@omphteliba, @gharlan)
* Neuer EP `ART_CONTENT_UPDATED` bei jeglichen Content-Änderungen (@gharlan)
* In der Struktur wird nicht mehr die Kategorie-Zeile ".." für die Oberkategorie ausgegeben (@schuer)
* Die Artikel-Tabellenzeilen haben ein neues Attribut `data-article-id="X"` für Artikelspezifische Anpassungen (@skerbis)
* Module-Auswahl über separates Fragment `module_select.php` für einfachere Anpassung (@tbaddade)
* Code besser strukturiert mittels neuer Klasse `rex_structure_context` (@DanielWeitenauer)
* Zusammenspiel der Plugins history und version optimiert (@dergel)
* Plugin history: Cronjob-Typ für das Löschen alter History-Datensätze (@dergel)
* Plugin version: Toolbar besser platziert nur über dem Bereich, auf den sie sich bezieht (@gharlan)

### Bugfixes

* Mountpoints wurden in Linkmap und `rex_category_select` unsortiert ausgegeben (@gharlan)
* `rex_category`: Wenn bei `getChildren`/`getArticles` ein leere Liste herauskam, wurde unnötig der Cache erneuert (@gharlan)
* `rex_article_content`: Bei `hasValue` konnte im Gegensatz zu `getValue` nicht der `art_`-Präfix für die Metainfos weggelassen werden (@bloep)
* Beim Ändern von Kategorien/Artikeln wurde das Änderungsdatum immer in allen Sprachen neu gesetzt (@gharlan)


Version 2.8.1 – 01.11.2019
--------------------------

### Security

* XSS Sicherheitslücken behoben (Michel Pass und Mathias Niedung von Althammer & Kill, @gharlan)


Version 2.8.0 – 20.08.2019
--------------------------

### Neu

* Assets nutzen immutable cache (@staabm)
* `rex_navigation`: Methode `addCallback` gibt `$this` zurück (@alexplusde)
* EP `CAT_MOVED`: "clang"-Parameter wird übergeben analog zu anderen EPs ("clang_id" ist deprecated) (@gharlan)
* Bei Template-/Modul-Namen wird Hinweis ausgeben, dass `translate:i18n_key`-Syntax verwendet werden kann (@tbaddade)
* Slice-Ansicht: "Bearbeiten"/"Löschen" ausgeschrieben, statt Icons (@alexplusde)
* Linkmap: IDs hinter Namen optisch zurückgenommen (@tbaddade)

### Bugfixes:

* EP `SLICE_ADDED`: `slice_id`-Parameter war immer `0` (@staabm)
* PlugIn `version`: In Tablet-Ansicht wurden die Buttons nicht angezeigt (@tbaddade)
* `$this->getValue('createdate')` lieferte im Backend einen Datetime-String, statt des Unix-Timestamps wie im Frontend (@gharlan)
* `rex_template::getTemplatesForCategory`: Bei `$ignore_inacttive=false` wurden nur inaktive Templates geliefert, statt alle (@gharlan)
* Nach Prio-Setzung wurde nicht der Cache aller betroffenen Kategorien/Artikel zurückgesetzt (@gharlan)


Version 2.7.0 – 12.03.2019
--------------------------

### Neu

* Neuer EP: `CAT_MOVED` (@bloep)
* Linkmap öffnet default in aktueller Kategorie (@schuer)
* version-PlugIn: Arbeitsversion kann geleert werden (@dpf-dd)
* Slice-Value-Felder als `MEDIUMTEXT` statt `TEXT` damit mehr Inhalte gespeichert werden können (@bloep)
* Leere CTypes werden in der Backend-Navi grau dargestellt (@schuer)
* Sliceausgabe mit Scrollbar bei zu breiten Inhalten (@schuer)
* In Moduleverwaltung wird angezeigt, ob die Module jeweils in Verwendung sind (@tbaddade)
* Bei (Re)Installation/Update wird `rex_sql_table` verwendet (@bloep)
* "Kein Startartikel selektiert"-Fehler nutzt Frontend-Ooops-Seite (@tbaddade)

### Bugfixes:

* `rex_category::get()` lieferte auch für Nicht-Startartikel ein Kategorie-Objekt (@gharlan)
* `rex_category::getCurrent()` lieferte Fehler, wenn es keinen aktuellen Artikel gibt (@gharlan)
* Der Funktionen-Tab wurde nicht ausgeblendet, wenn ein Benutzer nur die Berechtigung für `copyContent[]` und nur für eine Sprache hat (@TobiasKrais)
* Template-Verwaltung: An einer Stelle wurde der Table-Prefix `rex_` fix genommen, statt `rex::getTablePrefix()` (@staabm)
* In Modulen enthielt im Backend die Variable `$content` den Modul-PHP-Code, was zu verwirrenden Ausgaben führen konnte (@gharlan)


Version 2.6.1 – 21.06.2018
--------------------------

### Bugfixes

* Bei der Status-Änderung von Kategorien wurde fälschlich das `publishArticle[]`-Recht statt `publishCategory[]` geprüft (@gharlan)


Version 2.6.0 – 05.06.2018
--------------------------

### Neu

* Mountpoints werden nach Prio sortiert, wenn alle in gleicher Oberkategorie (@gharlan)
* Neue EPs `ART_MOVED` und `ART_COPIED` (@alexwenz)
* Template löschen: Hinweis welche Artikel es benutzen (@bloep)
* Linklist: Artikel-IDs werden auch ausgegeben (@tbaddade)
* Umbenennung "Homepage" in "Hauptebene" in Breadcrumb + passenderes Icon (@tbaddade)
* Umbenennung "Spalten" in "Bereiche" (@alexplusde)
* `rex_category`, `rex_article`, `rex_article_base`: Neue Methode `getClangId`, `getClang` als deprecated markiert (@staabm)
* Rechtschreibprüfung in Codeeingabefeldern deaktiviert (@staabm)

### Bugfixes

* Kompatibilität zu PHP 7.2 (@IngoWinter)
* Bei der Ausgabe des Modulnamens fehlte teils das Escaping (@staabm)


Version 2.5.0 – 21.12.2017
--------------------------

### Neu

* CSRF-Schutz für Api-Functions, Templates und Module (@gharlan)
* Neue EPs `MODULE_ADDED/UPDATED/DELETED` und `TEMPLATE_ADDED/UPDATED/DELETED` (@bloep)
* Bessere Code-Strukturierung (Api-Functions) (@DanielWeitenauer)


Version 2.4.0 – 04.10.2017
--------------------------

### Security

* XSS-Möglichkeit in Linkmap beseitigt (@staabm)

### Neu

* history-Plugin: Slider mit Visualisierung, zu welchen Zeitpunkten Snapshots gemacht wurden (@schuer)
* Funktionen-Subpage wird nur angezeigt, wenn die Rolle für mindestens eine der Funktionen die Berechtigung hat (@DanielWeitenauer)
* Modul-/Template-Liste: 100 pro Seite (@gharlan)
* EP `SLICE_SHOW` enthält nun Parameter `sql` für direkten Zugriff auf alle Values (@dergel)
* `rex_redirect`: Verständliche Exception wenn fälschlich eine URL statt einer Artikel-ID übergeben wird (@joachimdoerr)

### Bugfixes

* history-Plugin:
    - Wiederherstellung funktionierte nicht (@skerbis)
    - Besserer Spaltenabgleich zwischen History- und Haupttabelle (@dergel)
* `rex_template` hat teilweise Notices geworfen (@DavidBruchmann)
* Teilweise kam die unübersetzte Meldung "translate:article_doesnt_exist" (@TobiasKrais)
* Modul-/Template-Liste: Beim Speichern landete man immer auf Seite 1 (@gharlan)
* Benutzer mit `article2category[]`-Recht konnten fälschlich keine Kategorien in Artikel umwandeln (@gharlan)


Version 2.3.1 – 19.03.2017
--------------------------

### Bugfixes

* Bei Nutzung der Linkmap über Editoren (Redactor etc.) wurde der Link teilweise mehrfach eingefügt
* In den Service-Klassen wurden teilweise Int-Parameter ungeprüft in Queries genutzt
* Es kam zu einem Fehler beim Updaten, wenn das History-Plugin installiert, aber nicht aktiviert ist
* In der Modulliste wurde der Name nicht übersetzt


Version 2.3.0 – 14.02.2017
--------------------------

### Security

* Bei Backend-Benutzern war über die Linkmap Cross-Site-Scripting (XSS) möglich

### Neu

* Beim Block-Übernehmen wird die Meldung auch im Block angezeigt
* Nach Durchführung von Artikelfunktionen bleibt man auf der Funktionsseite
* History: Benutzer wird mit protokolliert
* Neue Methode rex_article_slice::getPriority() (@phoebusryan)
* MEDIUMTEXT-Spalten für Template- und Module-Code

### Bugfixes

* Wenn Frontend mit nicht vorhandener ID als clang-Parameter aufgerufen wurde, kam es zu einem harten Fehler, statt Umleitung auf NotFound-Artikel
* Kategorie/Artikel verschieben: Breadcrumb wurde nicht aktualisiert
* Inhalte kopieren:
    - Wenn im Zielartikel bereits Slices vorhanden waren, wurden die neuen nicht korrekt ans Ende gesetzt
    - Wenn Ursprung keine Slices enthielt, kam es zu einer falschen Fehlermeldung
* Bei Reload nach Block-Übernehmen blieb der Block nicht offen
* Beim Anlegen neuer Slices funktionierten REX_MODULE_ID und REX_CTYPE_ID nicht
* History:
    - Teilweise wurden Versionen in Dropdown doppelt angezeigt
    - Bei Multidomain-Lösungen konnten die Artikel der anderen Domains nicht angezeigt werden
* rex_navigation::getBreadcrumb(): Die Start-Kategorie-ID wurden nicht berücksichtigt
* rex_navigation::showBreadcrumb(): Die Parameter waren falsch benannt (versetzt)


Version 2.2.0 – 15.07.2016
--------------------------

### Neu

* Neues Plugin "history": Änderungen an Artikelinhalten werden protokolliert, mit Vergleichs- und Wiederherstellungsmöglichkeit
* Das Default-Template wird vorinstalliert
* Neue EPs: SLICE_CREATE, SLICE_EDIT, SLICE_DELETE, SLICE_MOVE, ART_SLICES_QUERY, ART_SLICES_COPY
* Umbenennung EPs (alte funktionieren noch): STRUCTURE_CONTENT_CREATED/UPDATED/DELETED -> SLICE_CREATED/UPDATED/DELETED
* Service-Klassen können auch aus Frontend heraus genutzt werden
* "Block hinzufügen" wird auch beim Bearbeiten/Erstellen eines Slices angezeigt

### Bugfixes

* Artikel kopieren: Inhalten wurden nicht kopiert, wenn Ctype 1 leer ist
* Content-Page: Es wurde nicht immer korrekt zum Slice gesprungen
* Benutzer mit Recht "Artikel veröffentlichen" konnten trotzdem den Status der Artikel nicht ändern
* Bei Artikeln ohne Template wurde "KEIN TEMPLATE" nicht mehr angezeigt, sobald es Templates gab
* Sobald eine 2. Seite vorhanden war, bekamen alle neuen Artikel/Kategorien die Prio 31
* $art/$cat->getValue('parent_id') liefert immer das selbe wie getParentId()
* Aktionen: "Aktion übernehmen" sprang zurück in Übersicht


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* Slice-Values werden automatisch zurückgesetzt, wenn nicht gesendet (dadurch Checkboxes ohne Tricks möglich)
* JS-Event rex:selectLink bei Auswahl eines Artikels in Linkmap
* Templates-Page: Aufteilung in Tabs
* Content-Subpages können sich auch links dranheften und href überschreiben

### Bugfixes

* CAT_UPDATED: Es wurden teilweise die alten Daten geliefert
* REX_LINK[]: Wenn Feld leer, wurde fälschlicherweise die aktuelle URL geliefert
* Linkmap: Bei Benutzern mit eingeschränkten Kategorierechten kam es zu Fehlern
* POSTSAVE-Actions hatten keinen Zugriff auf Values


Version 2.0.1 – 09.02.2016
--------------------------

### Bugfixes

* Die CommonVars (REX_ARTICLE_ID etc.) konnten nicht innerhalb der ObjectVars (REX_ARTICLE[] etc.) verwendet werden (@schuer)

