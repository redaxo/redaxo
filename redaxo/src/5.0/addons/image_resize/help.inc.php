<?php
/**
 * Image-Resize Addon
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

<p>Addon zum generieren von Vorschaugrafiken.</p>

<h3>Benutzung:</h3>
<p>
  Um eine Vorschaugrafik zu generieren wird es durch Aufrufen mit einer speziellen Url umgewandelt, z.B:
  <b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=100w__imagefile</b>
</p>

<h3>Modi:</h3>
<p>
w = width       (Skalieren in der Breite)<br />
h = height      (Skalieren in der Höhe)<br />
c = crop        (Bildausschnitt herausschneiden)<br />
a = automatic   (Skalieren anhand der längsten Seite des Bildes)
</p>

<h3>Filter/Effekte:</h3>
<p>
blur<br />
brand<br />
sepia<br />
sharpen<br />
grayscale
</p>

<h3>Anwendungsbeispiele:</h3>
<p>
Skaliere das Bild auf eine Breite von 100px. Die Proportionen des Bildes werden beibehalten.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=100w__imagefile</b>

<br /><br />
Skaliere das Bild auf eine Höhe von 150px. Die Proportionen des Bildes werden beibehalten.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=150h__imagefile</b>

<br /><br />
Skaliere das Bild anhand der längsten Seite, diese auf eine Länge von 200px. Die Proportionen des Bildes werden beibehalten.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=200a__imagefile</b>

<br /><br />
Skaliere das Bild auf eine Breite von 100px und eine Höhe von 200px. Ggf. wird das Bild dadurch verzehrt..<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=100w__200h__imagefile</b>

<br /><br />
Schneide aus dem Bild, ausgehend vom Zentrum, einen 100px Breiten und 200px hohen Bereich heraus.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=100c__200h__imagefile</b>

<br /><br />
Schneide aus dem Bild, ausgehend vom Zentrum um 50px nach rechts verschoben, einen 100px Breiten und 200px hohen Bereich heraus.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=100c__200h__50o__imagefile</b>

<br /><br />
Schneide aus dem Bild, ausgehend vom Zentrum um 150px nach links verschoben, einen 100px Breiten und 200px hohen Bereich heraus.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=100c__200h__-150o__imagefile</b>

<br /><br />
Wende die Filter blur und sepia auf das Bild an. Zugleich wird das Bild an der längsten Seite auf 200px länge skaliert. Die Proportionen des Bildes werden beibehalten.<br />
<b><?php echo $REX["FRONTEND_FILE"]; ?>?rex_resize=200a__imagefile&amp;rex_filter[]=blur&amp;rex_filter[]=sepia</b>

</p>