Changelog
=========

Version 2.3.0 – 05.06.2018
--------------------------

### Neu

* Command `cronjob:run`: Es kann ein einzelner Job direkt ausgeführt werden (`--job`) (@gharlan)

### Bugfixes

* Status-Toggle-Link war nicht nutzbar (CSRF-Token fehlte) (@gharlan)


Version 2.2.0 – 21.12.2017
--------------------------

### Neu

* CSRF-Schutz (@gharlan)
* Consolen-Command `cronjob:run` für die Ausführung der Jobs der script-Umgebung (das alte Skript unter `redaxo/src/addons/cronjob/bin/run` ist deprecated) (@staabm)

### Bugfixes

* Fehler werden besser abgefangen, vor allem um die Ausführung weiterer Jobs in der cli nicht zu behindern (@staabm)


Version 2.1.2 – 04.10.2017
--------------------------

### Bugfixes

* Bedingte typspezifische Parameter wurden nicht getoggelt (@gharlan)


Version 2.1.1 – 14.02.2017
--------------------------

### Bugfixes

* Langlaufende Jobs (>2h) oder bei Abbrüchen wurden die Jobs nach 2 Stunden erneut gestartet
* Wenn keine Umgebung auswählt wurde, kam es zu einem Fehler ohne sinnvolle Meldung
* Wenn die Skript-Umgebung mit einer zu niedrigen PHP-Version genutzt wurde, wurde mit unverständlicher Meldung abgebrochen


Version 2.1.0 – 30.09.2016
--------------------------

### Neu

* Neue Cronjob-Umgebung "Skript", insbesondere um die Redaxo-Cronjobs über 
  einen echten Cronjob laufen zu lassen
* Flexiblere Intervallauswahl

### Bugfixes

* Cronjob-Typ "phpcallback" hat teilweise Notices geworfen


Version 2.0.3 – 15.07.2016
--------------------------

### Bugfixes

* Manuelles Ausführen von Cronjobs nicht über pjax, da es länger dauern kann


Version 2.0.2 – 24.03.2016
--------------------------

### Bugfixes

* Bei Nutzung von MariaDB wurden die Cronjobs teilweise nicht ausgeführt
