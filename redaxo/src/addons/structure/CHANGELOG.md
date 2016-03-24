Changelog
=========

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

