<?php

$export_addon_dir = rex_path::addon('import_export');
require_once $export_addon_dir . '/functions/function_folder.php';
require_once $export_addon_dir . '/functions/function_import_folder.php';
require_once $export_addon_dir . '/functions/function_import_export.php';

/**
 * @package redaxo\core
 */
class rex_setup_importer
{
    public static function updateFromPrevious()
    {
        // ----- vorhandenen seite updaten
        $err_msg = '';

        $import_sql = rex_path::core('install/update4_x_to_5_0.sql');
        if ($err_msg == '') {
            $err_msg .= self::import($import_sql);
        }

        if ($err_msg == '') {
            $err_msg .= self::installAddons();
        }

        return $err_msg;
    }

    public static function loadExistingImport($import_name)
    {
        // ----- vorhandenen Export importieren
        $err_msg = '';

        if ($import_name == '') {
            $err_msg .= '<p>' . rex_i18n::msg('setup_508') . '</p>';
        } else {
            $import_sql = getImportDir() . '/' . $import_name . '.sql';
            $import_archiv = getImportDir() . '/' . $import_name . '.tar.gz';

            // Nur hier zuerst die Addons installieren
            // Da sonst Daten aus dem eingespielten Export
            // Überschrieben würden
            // Da für das Installieren der Addons die rex_config benötigt wird,
            // mit overrideExisting() eine saubere, komplette Basis schaffen
            if ($err_msg == '') {
                $err_msg .= self::overrideExisting();
            }
            if ($err_msg == '') {
                $err_msg .= self::import($import_sql, $import_archiv);
            }
        }

        return $err_msg;
    }

    public static function databaseAlreadyExists()
    {
        // ----- db schon vorhanden, nichts tun
        return self::installAddons(false, false);
    }

    public static function overrideExisting()
    {
        // ----- volle Datenbank, alte DB löschen / drop
        $err_msg = '';

        $import_sql = rex_path::core('install.sql');

        $db = rex_sql::factory();
        foreach (self::getRequiredTables() as $table) {
            $db->setQuery('DROP TABLE IF EXISTS `' . $table . '`');
        }

        if ($err_msg == '') {
            $err_msg .= self::import($import_sql);
        }

        if ($err_msg == '') {
            $err_msg .= self::installAddons(true);
        }

        return $err_msg;
    }

    public static function prepareEmptyDb()
    {
        // ----- leere Datenbank neu einrichten
        $err_msg = '';
        $import_sql = rex_path::core('install.sql');

        if ($err_msg == '') {
            $err_msg .= self::import($import_sql);
        }

        $err_msg .= self::installAddons();

        return $err_msg;
    }

    public static function verifyDbSchema()
    {
        $err_msg = '';

        // Prüfen, welche Tabellen bereits vorhanden sind
        $existingTables = [];
        foreach (rex_sql::showTables() as $tblname) {
            if (substr($tblname, 0, strlen(rex::getTablePrefix())) == rex::getTablePrefix()) {
                $existingTables[] = $tblname;
            }
        }

        foreach (array_diff(self::getRequiredTables(), $existingTables) as $missingTable) {
            $err_msg .= rex_i18n::msg('setup_502', $missingTable) . '<br />';
        }
        return $err_msg;
    }

    private static function getRequiredTables()
    {
        return [
            rex::getTablePrefix() . 'clang',
            rex::getTablePrefix() . 'user',
            rex::getTablePrefix() . 'config',
        ];
    }

    private static function import($import_sql, $import_archiv = null)
    {
        $err_msg = '';

        if (!is_dir(rex_path::addon('import_export'))) {
            $err_msg .= rex_i18n::msg('setup_510') . '<br />';
        } else {
            if (file_exists($import_sql) && ($import_archiv === null || $import_archiv !== null && file_exists($import_archiv))) {
                rex_i18n::addDirectory(rex_path::addon('import_export', 'lang/'));

                // DB Import
                $state_db = rex_a1_import_db($import_sql);
                if ($state_db['state'] === false) {
                    $err_msg .= nl2br($state_db['message']) . '<br />';
                }

                // Archiv optional importieren
                if ($state_db['state'] === true && $import_archiv !== null) {
                    $state_archiv = rex_a1_import_files($import_archiv);
                    if ($state_archiv['state'] === false) {
                        $err_msg .= $state_archiv['message'] . '<br />';
                    }
                }
            } else {
                $err_msg .= rex_i18n::msg('setup_509') . '<br />';
            }
        }

        return $err_msg;
    }

    // -------------------------- System AddOns prüfen
    private static function installAddons($uninstallBefore = false, $installDump = true)
    {
        $addonErr = '';
        rex_package_manager::synchronizeWithFileSystem();

        if ($uninstallBefore) {
            foreach (array_reverse(rex_package::getSystemPackages()) as $package) {
                $manager = rex_package_manager::factory($package);
                $state = $manager->uninstall($installDump);

                if ($state !== true) {
                    $addonErr .= '<li>' . $package->getPackageId() . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
                }
            }
        }
        foreach (rex::getProperty('system_addons') as $packageRepresentation) {
            $state = true;
            $package = rex_package::get($packageRepresentation);
            $manager = rex_package_manager::factory($package);

            if ($state === true && !$package->isInstalled()) {
                $state = $manager->install($installDump);
            }

            if ($state !== true) {
                $addonErr .= '<li>' . $package->getPackageId() . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
            }

            if ($state === true && !$package->isAvailable()) {
                $state = $manager->activate();

                if ($state !== true) {
                    $addonErr .= '<li>' . $package->getPackageId() . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
                }
            }
        }

        if ($addonErr != '') {
            $addonErr = '<ul class="rex-ul1">
            <li>
            <h3 class="rex-hl3">' . rex_i18n::msg('setup_513', '<span class="rex-error">', '</span>') . '</h3>
            <ul>' . $addonErr . '</ul>
            </li>
            </ul>';
        }

        return $addonErr;
    }
}
