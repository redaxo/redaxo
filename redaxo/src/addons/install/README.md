# Installer

With the installer, add-ons can be downloaded and updated from the download area at [www.redaxo.org](https://www.redaxo.org).

Depending on the settings, a backup of the old add-on folder is made during the update, however, a database backup is not made. If the add-on stores settings directly in the add-on folder, these will be lost during an update.

Add-on developers should no longer store settings in the `config.inc.php` or in other files within the add-on folder. Instead, the data folder `/redaxo/include/data/addons/addonkey` should be used.
Furthermore, an update script `update.inc.php` can be added to the add-on, which makes changes to the database etc. during the update.

If the user and the API key for myREDAXO are stored in the settings, you can also upload your own add-ons to the download area via the installer. The API key can be viewed in the logged-in area at [https://www.redaxo.org/de/myredaxo/mein-api-key/](https://www.redaxo.org/de/myredaxo/mein-api-key/). Since the key can be used to change your own add-ons via the API, the key should not be shared.
