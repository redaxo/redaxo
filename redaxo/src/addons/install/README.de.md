# Installer

Mit dem Installer können AddOns aus dem Downloadbereich auf [www.redaxo.org](https://www.redaxo.org) heruntergeladen und aktualisiert werden.

Bei der Aktualisierung wird je nach Einstellung ein Backup des alten AddOn-Ordners angelegt, ein Datenbankbackup wird jedoch nicht ausgeführt. Falls das AddOn Einstellungen direkt im AddOn-Ordner ablegt, gehen diese bei einem Update verloren.

AddOn-Entwickler sollten Einstellungen nicht mehr in der `config.inc.php` oder in anderen Dateien innerhalb des AddOn-Ordners ablegen. Stattdessen sollte der Data-Ordner `/redaxo/include/data/addons/addonkey` verwendet werden.
Des Weiteren kann dem AddOn ein Updateskript `update.inc.php` beigelegt werden, welches während des Updates Änderungen an der Datenbank etc. durchführt.

Wenn in den Einstellungen der Benutzer und der API-Key für myREDAXO hinterlegt werden, können über den Installer auch die eigenen Addons in den Downloadbereich hochgeladen werden. Der API-Key ist im eingeloggten Bereich unter [https://www.redaxo.org/de/myredaxo/mein-api-key/](https://www.redaxo.org/de/myredaxo/mein-api-key/) einzusehen. Da mit dem Key die eigenen AddOns über die API verändert werden können, sollte der Key nicht weitergegeben werden.
