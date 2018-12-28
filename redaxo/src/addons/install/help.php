<p>
    Mit dem Installer können AddOns aus dem Downloadbereich auf <a href="https://www.redaxo.org">www.redaxo.org</a> heruntergeladen und aktualisiert werden.<br />
    Bei der Aktualisierung wird je nach Einstellung ein Backup des alten AddOn-Ordners gemacht, ein Datenbankbackup wird jedoch nicht ausgeführt. Falls das AddOn Einstellungen direkt im AddOn-Ordner ablegt, gehen diese bei einem Update verloren.
</p>

<p>
    AddOn-Entwickler sollten Einstellungen nicht mehr in der <code>config.inc.php</code> oder in anderen Dateien innerhalb des AddOn-Ordners ablegen. Stattdessen sollte der Data-Ordner (<code>/redaxo/include/data/addons/&gt;addonkey&lt;</code> verwendet werden.<br />
    Des Weiteren kann dem AddOn ein Updateskript (<code>update.inc.php</code>) beigelegt werden, welches während des Updates Änderungen an der Datenbank etc. durchführt.
</p>

<p>
    Wenn in den Einstellungen der Benutzer und der Api-Key für myREDAXO hinterlegt werden, können über den Installer auch die eigenen Addons in den Downloadbereich hochgeladen werden. Der Api-Key ist im eingeloggten Bereich unter <a href="https://www.redaxo.org/de/myredaxo/mein-api-key/">https://www.redaxo.org/de/myredaxo/mein-api-key/</a> einzusehen. Da mit dem Key die eigenen AddOns über die Api verändert werden können, sollte der Key nicht weitergegeben werden.
</p>
