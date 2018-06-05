Changelog
=========

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
