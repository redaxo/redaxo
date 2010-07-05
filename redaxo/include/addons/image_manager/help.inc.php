<?php
/**
 * image_manager Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author <a href="http://www.vscope.at">www.vscope.at</a>
 *
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 *
 * @package redaxo4
 * @version svn:$Id$
 */
?>
<h3>Funktionen:</h3>

<p>Addon zum generieren von Grafiken anhand von Bildtypen.</p>

<h3>Benutzung:</h3>
<p>
Die Bildtypen werden in der Verwaltung des Addons erstellt und konfiguriert.
Jeder Bildtyp kann beliebig viele Effekte enthalten, die auf das aktuelle Bild angewendet werden.

Zum einbinden eines Bildes muss dazu der Bildtyp in der Url notiert werden.
</p>

<h3>Anwendungsbeispiele:</h3>
<p>
  <?php echo $REX["FRONTEND_FILE"]; ?>?rex_img_type=ImgTypeName&rex_img_file=ImageFileName
</p>