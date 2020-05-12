Changelog
=========

Version 2.6.2 – 08.05.2020
--------------------------

### Bugfixes

* EP `USER_UPDATED`: Parameter `id` war immer `0` (@gharlan)


Version 2.6.0 – 02.02.2020
--------------------------

### Neu

* Perm-Selects in Rollenverwaltung:
    - Perms mit vorangestellten Perm-Key (`perm[]`) und alphabetisch sortiert (@tbaddade)
    - Bei Bedarf bis zu 20 Zeilen lang statt 10 (@gharlan)
* Bei (Re)Installation/Update wird `rex_sql_table` verwendet (@tbaddade)

### Bugfixes

* Bei Benutzern mit mehreren Rollen konnte es bei den complex_perms (z.B. Mountpoints) zu Dopplungen kommen (@gharlan)


Version 2.5.3 – 01.11.2019
--------------------------

### Security

* XSS Sicherheitslücken behoben (Michel Pass und Mathias Niedung von Althammer & Kill, @gharlan)


Version 2.5.2 – 12.03.2019
--------------------------

### Bugfixes

* Bei Uhrzeit/Zeitzonen-Differenzen zwischen PHP und DB wurde der letzte Login falsch angezeigt (@gharlan)
* Mit MySQL 8.0 bis 8.0.12 kam es in der Benutzerliste zu einem Fehler (@schuer)
* "Rolle(n)" statt "Rolle" als Label, da mehrere ausgewählt werden können (@skerbis)


Version 2.5.1 – 01.10.2018
--------------------------

### Security

* Sicherheitslücken (SQL-Injection) in der Benutzerverwaltung geschlossen (gemeldet von @Balis0ng, ADLab of VenusTech) (@staabm)


Version 2.5.0 – 05.06.2018
--------------------------

### Neu

* Login-Fehlversuche können zurückgesetzt werden (@gharlan)
* Benutzerliste sortierbar nach Spalten (@gharlan)
* Rollen werden nach Name sortiert (@tbaddade)

### Bugfixes

* Kompatibilität zu PHP 7.2 (@gharlan)
* Wenn man bei Admins die Admin-Checkbox abhakt, erschien nicht das Rollen-Auswahlfeld (@palber)
* Die Perms enthielten teils sichtbare HTML-Entities (Doppel-Escaping) (@gharlan)


Version 2.4.0 – 21.12.2017
--------------------------

### Neu

* CSRF-Schutz (@gharlan)


Version 2.3.0 – 04.10.2017
--------------------------

### Neu

* Neue Extension Points: USER_ADDED, USER_UPDATED, USER_DELETED

### Bugfixes

* Login-Name wurde in Liste nicht escaped (@gharlan)
* Beim Anlegen neuer Benutzer wurde das Passwort teilweise vorbelegt mit dem im Browser gespeicherten Passwort (@gharlan)
* Initial wurde immer das dritte Eingabefeld (Benutzername) fokussiert (@gharlan)


Version 2.2.0 – 14.02.2017
--------------------------

### Neu

* Benutzer können mehrere Rollen bekommen


Version 2.1.3 – 06.12.2016
--------------------------

* Beim sich selbst Bearbeiten verlor man den Admin-Status


Version 2.1.2 – 19.09.2016
--------------------------

* Beim Bearbeiten von Benutzern wurden diese immer zu Admins


Version 2.1.1 – 15.07.2016
--------------------------

* Bei Fehlern werden abgesendete Werte wieder angezeigt
* E-Mail-Adresse wird validiert
* Nicht-Admins sehen Admin-Checkbox gar nicht mehr


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* E-Mail-Feld bei Benutzern (optional)
* Rolle wird in Benutzerliste angezeigt

### Bugfixes

* Checkbox-Status ("Alle") wurde nach Speichern falsch angezeigt
