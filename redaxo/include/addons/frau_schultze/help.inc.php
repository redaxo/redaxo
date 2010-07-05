<?php

/**
 * Url Marketing Addon - "Frau Schultze"
 *
 * @author kai.kristinus[at]yakamara[dot]de - Kai Kristinus
 * @author <a href="http://www.yakamara.de/">yakamara</a>
 * 
 * @author mail[at]blumbeet[dot]com Thomas Blum
 * @author <a href="http://www.blumbeet.com/">blumbeet - web.studio</a>
 *
 * @package redaxo4
 * @version svn:$Id$
 */
?>

<h3 class="rex-hl4">Autoren</h3>

<ul>
	<li><strong>Kai Kristinus</strong> - kai.kristinus[at]yakamara.de<br />Idee und erste Version<br /><a href="http://www.yakamara.de/">www.yakamara.de</a></li>
	<li><strong>Thomas Blum</strong> - mail[at]blumbeet.com<br />Addonaufbau und Erweiterung der Table Urls<br /><a href="http://www.blumbeet.com/">www.blumbeet.com</a></li>
</ul>


<h3 class="rex-hl4">Beschreibung</h3>

<h4 class="rex-hl3">Marketing Urls</h4>
<p>Es können sogenannte Marketing Urls definiert werden. D.h. das unter der Domain http://redaxo.de/frau_schultze.html ein anderer Artikel tatsächlich angezeigt (Url bleibt bestehen) oder aber auf einen anderen Artikel http://redaxo.de/service/frau/schultze.html weitergeleitet werden kann.</p>


<h4 class="rex-hl3">Tabellen Urls</h4>
<p>Bei Tabellen Urls handelt es ich um Urls die aus einer bestimmten Datenbanktabelle generiert werden.<br />Dazu werden 2 Spalten benötigt. Die erste um die <strong>eindeutige Id</strong> zu definieren und eine zweite aus der die <strong>Urls generiert</strong> werden</p>

<p>Mit folgenden Code bekommt man die eindeutige Id zu der Url.</p>

<?php

$module = '<?php
$tmp = parse_url($_SERVER[\'REQUEST_URI\']);
$myurl = $tmp[\'path\'];

$myid = (int)a724_getTableId(\'TABELLENNAME\', $myurl);
?>';
?>

<?php rex_highlight_string($module); ?>

<p>Ein Beispiel des Addons findet sich auf <a href="http://www.elbepark.info/de/shops-und-mehr/elbeparkfinder.html">www.elbepark.info</a>. Alle hier verlinkten Mieter sind in einer Tabelle vorhanden und verweisen auf <strong>denselben</strong> Redaxo Artikel.</p>



<h3 class="rex-hl4">Voraussetzungen</h3>

<p>Das AddOn benötigt die Redaxo SVN Version (Rev 1821) und die Url-Rewrite-Fullnames Klasse. Mit der Version 4.3b5 funktioniert es nicht, da in der fullnames Klasse der EP noch nicht dabei ist</p>



<h3 class="rex-hl4">Todo</h3>

<p>Marketing Urls cachen</p>