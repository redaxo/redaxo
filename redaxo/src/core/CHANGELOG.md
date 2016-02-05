Changelog
=========

Version 5.0.1 – XX.XX.2016
--------------------------

### Bugfixes

* Einbindung von Templates/Modulen (rex_stream) funktionierte auf manchen Servern nicht
* REX_VARs:
    - Verschachtelte REX_VARs funktionierten nicht bei Nutzung der globalen Args (ifempty, prefix etc.)
    - Teilweise verschwanden direkt auf die Vars folgende Zeilenumbrüche
* Der Error-Handler kam nicht mit den neuen Error-Klassen in PHP 7 klar
