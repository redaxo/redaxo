/**
 * URL-Rewrite Addon
 * @author markus.staab[at]redaxo[dot]de Markus Staab
 * @package redaxo4.2
 * @version svn:$Id$
 */

<a href="?page=addon&amp;spage=help&amp;addonname=url_rewrite&amp;mode=changelog">Changelog</a>

<strong>Beschreibung:</strong>

Dieses Addon ermöglicht es eigene URL Rewrites zu erstellen.

Diese können via Server ausgewertet (apache mod_rewrite) 
oder auch selbst via PHP interpretiert werden.

D.h. Es kann auch ein Rewriting der URL durchgeführt werden,
ohne das der Server etwaige Module dafür bereitstellen muss.

<strong>Download:</strong>

<a href="http://www.redaxo.de/18-0-addons.html">REDAXO Addon-Sammlung</a>


<strong>Konfiguration:</strong>

Die verschiedenen Rewriter Klassen funktionieren nur mit 
bestimmten Serverkonfigurationen. Um z.b. den Rewriter 
<em>class.rewrite_fullnames</em> oder <em>class.rewrite_mod_rewrite</em>
verwenden zu können, muss der Apache Server mit 
dem Modul "mod_rewrite" konfiguriert werden. Wenn Sie dazu 
Fragen haben, sollten sie sich mit Ihrem Provider in verbindung setzen.

Die Standard Rewriter Klasse <em>class.rewrite_simple</em> sollte nur eingesetzt
werden, wenn der Server <em>nicht</em> mit dem Modul "mod_rewrite" ausgestattet ist!


<strong>Installation:</strong>

- Unter "redaxo/include/addons" einen Ordner "url_rewrite" anlegen

- Alle Dateien des Archivs nach "redaxo/include/addons/url_rewrite" entpacken

- Im Redaxo AddOn Manager das Plugin installieren

- Im Redaxo AddOn Manager das Plugin aktivieren

- Im Header des Templates die Zeile &lt;base href="http://www.example.org/" /&gt; hinzufügen
   <em>Url unter der dein Frontend erreichbar ist!Der Base-Href muss am Anfang des HEAD stehen!</em>

- In der Datei "redaxo/include/addons/config.inc.php" die Zeile
  <em>require_once $UrlRewriteBasedir.'/classes/class.rewrite_simple.inc.php';</em>
	Mit dem gewünschen Rewriter ersetzen, <strong>z.B:</strong>
  <em>require_once $UrlRewriteBasedir.'/classes/class.rewrite_fullname.inc.php';</em>
  
- /.htaccess Datei anpassen (Beispielkonfigurationen befinden sich in dem jeweiligen Rewriter)

- Unter "Specials" mit "Regeneriere Artikel &amp; Cache" den Artikel Cache regenerieren

- fertig ;)


<strong>Todo:</strong>

- Weitere Rewrites implementieren

- Evtl. Backend Pages


<strong>Credits:</strong>

Vielen dank an alle die Bugs gemeldet oder Verbesserungsvorschläge gegeben haben.
    
<strong>Besonderen Dank geht an:</strong>

<a href="http://www.vscope.at">vscope - http://www.vscope.at</a>