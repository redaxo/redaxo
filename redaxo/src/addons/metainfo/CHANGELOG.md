Changelog
=========

Version 2.3.1 – 05.06.2018
--------------------------

### Bugfixes

* Beim Bearbeiten der Artikel-Metainfos wurden updatedate und updateuser nicht aktualisiert (@gharlan)
* Date/Time/Datetime-Felder: Tag/Monat/Stunde/Minute nun einheitlich zweistellig, aktueller Wert war vorher teils nicht selektiert (@gharlan)


Version 2.3.0 – 21.12.2017
--------------------------

### Neu

* CSRF-Schutz (bei Api-Functions) (@gharlan)

Version 2.2.0 – 04.10.2017
--------------------------

### Neu

* `rex_metainfo_add_field`: `callback` kann gesetzt werden (@DanielWeitenauer)

### Bugfixes

* Bei Checkboxen mit Value 0 wurde der `checked`-Status nicht richig gesetzt (@gharlan)


Version 2.1.1 – 14.02.2017
--------------------------

### Bugfixes

* Medienfelder in Sprachmetainfos wurden nicht beim Media-isInUse-Check berüchsichtigt
* Feld-id-Attribute waren zu unspezifisch, konnten daher doppelt vorkommen
* Bei Einzelcheckboxes stimmten id-Attribut und for-Attribut in Label nicht überein


Version 2.1.0 – 24.03.2016
--------------------------

### Neu

* Metainfos für Sprachen
* Es werden keine Felder inital angelegt, aber die Standardfelder können über einen Button nachgerüstet werden

### Bugfixes

* Bei Multiple-Selects wurde das size-Attribut falsch gesetzt
* Bei Artikel-Metainfos wurde keine Meldung nach Speichern angezeigt


Version 2.0.1 – 09.02.2016
--------------------------

### Bugfixes

* Bei Datumsfeldern war es nicht möglich, den Monat zu ändern (Gort)

