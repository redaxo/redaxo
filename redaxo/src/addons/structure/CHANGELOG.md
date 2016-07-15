Changelog
=========

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

