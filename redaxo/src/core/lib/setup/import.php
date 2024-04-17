<?php

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Addon\AddonManager;
use Redaxo\Core\Config;
use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;

/**
 * @internal
 */
class rex_setup_importer
{
    /**
     * @return string
     */
    public static function updateFromPrevious()
    {
        // ----- vorhandenen seite updaten
        $errMsg = '';

        $version = Core::getVersion();
        Core::setProperty('version', Core::getConfig('version'));

        try {
            include Path::core('update.php');
        } catch (rex_functional_exception $e) {
            $errMsg .= $e->getMessage();
        } catch (rex_sql_exception $e) {
            $errMsg .= 'SQL error: ' . $e->getMessage();
        }

        Core::setProperty('version', $version);

        if ('' == $errMsg) {
            $errMsg .= self::reinstallPackages();
        }

        return $errMsg;
    }

    /**
     * @param string $importName
     *
     * @return string
     */
    public static function loadExistingImport($importName)
    {
        if ('' == $importName || '/' === $importName) {
            return '<p>' . I18n::msg('setup_408') . '</p>';
        }

        // ----- vorhandenen Export importieren
        $errMsg = '';
        $importName = Path::basename($importName);

        $importSql = rex_backup::getDir() . '/' . $importName . '.sql';
        $importSql .= is_file($importSql) ? '' : '.gz';
        $importArchiv = rex_backup::getDir() . '/' . $importName . '.tar.gz';

        // Nur hier zuerst die Addons installieren
        // Da sonst Daten aus dem eingespielten Export
        // Überschrieben würden
        // Da für das Installieren der Addons die rex_config benötigt wird,
        // mit overrideExisting() eine saubere, komplette Basis schaffen
        $errMsg .= self::overrideExisting();

        if ('' == $errMsg) {
            $errMsg .= self::import($importSql, $importArchiv);
        }

        return $errMsg;
    }

    /**
     * @return string
     */
    public static function databaseAlreadyExists()
    {
        // ----- db schon vorhanden, nichts tun
        return self::reinstallPackages();
    }

    /**
     * @return string
     */
    public static function overrideExisting()
    {
        // ----- volle Datenbank, alte DB löschen / drop
        $errMsg = '';

        $db = Sql::factory();
        foreach (self::getRequiredTables() as $table) {
            $db->setQuery('DROP TABLE IF EXISTS `' . $table . '`');
        }

        try {
            include Path::core('install.php');
        } catch (rex_functional_exception $e) {
            $errMsg .= $e->getMessage();
        } catch (rex_sql_exception $e) {
            $errMsg .= 'SQL error: ' . $e->getMessage();
        }

        if ('' == $errMsg) {
            $errMsg .= self::installAddons(true);
        }

        return $errMsg;
    }

    /**
     * @return string
     */
    public static function prepareEmptyDb()
    {
        // ----- leere Datenbank neu einrichten
        $errMsg = '';

        try {
            include Path::core('install.php');
        } catch (rex_functional_exception $e) {
            $errMsg .= $e->getMessage();
        } catch (rex_sql_exception $e) {
            $errMsg .= 'SQL error: ' . $e->getMessage();
        }

        $errMsg .= self::installAddons();

        return $errMsg;
    }

    /**
     * @return string
     */
    public static function verifyDbSchema()
    {
        $errMsg = '';

        // Prüfen, welche Tabellen bereits vorhanden sind
        $existingTables = Sql::factory()->getTables(Core::getTablePrefix());

        foreach (array_diff(self::getRequiredTables(), $existingTables) as $missingTable) {
            $errMsg .= I18n::msg('setup_402', $missingTable) . '<br />';
        }
        return $errMsg;
    }

    /**
     * @return list<string>
     */
    private static function getRequiredTables()
    {
        return [
            Core::getTablePrefix() . 'clang',
            Core::getTablePrefix() . 'user_session',
            Core::getTablePrefix() . 'user_passkey',
            Core::getTablePrefix() . 'user',
            Core::getTablePrefix() . 'config',
        ];
    }

    private static function import(string $importSql, ?string $importArchive = null): string
    {
        $errMsg = '';

        if (is_file($importSql)) {
            I18n::addDirectory(Path::core('backup/lang/'));

            // DB Import
            $stateDb = rex_backup::importDb($importSql);
            if (!$stateDb['state']) {
                $errMsg .= nl2br($stateDb['message']) . '<br />';
            }

            // Archiv optional importieren
            if ($stateDb['state'] && null !== $importArchive && is_file($importArchive)) {
                $stateArchiv = rex_backup::importFiles($importArchive);
                if (!$stateArchiv['state']) {
                    $errMsg .= $stateArchiv['message'] . '<br />';
                }
            }
        } else {
            $errMsg .= I18n::msg('setup_409') . '<br />';
        }

        // Reload config from imported data
        Config::refresh();

        return $errMsg;
    }

    // -------------------------- System AddOns prüfen

    private static function installAddons(bool $uninstallBefore = false, bool $installDump = true): string
    {
        $addonErr = '';
        AddonManager::synchronizeWithFileSystem();

        if ($uninstallBefore) {
            foreach (array_reverse(Addon::getSystemAddons()) as $package) {
                $manager = AddonManager::factory($package);
                $state = $manager->uninstall($installDump);

                if (!$state) {
                    $addonErr .= '<li>' . $package->getPackageId() . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
                }
            }
        }
        foreach (Core::getProperty('system_addons') as $packageRepresentation) {
            $state = true;
            $package = Addon::require($packageRepresentation);
            $manager = AddonManager::factory($package);

            if (!$package->isInstalled()) {
                $state = $manager->install($installDump);
            }

            if (!$state) {
                $addonErr .= '<li>' . rex_escape($package->getPackageId()) . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
            }

            if ($state && !$package->isAvailable()) {
                $state = $manager->activate();

                if (!$state) {
                    $addonErr .= '<li>' . rex_escape($package->getPackageId()) . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
                }
            }
        }

        if ('' != $addonErr) {
            $addonErr = '<ul class="rex-ul1">
            <li>
            <h3 class="rex-hl3">' . I18n::msg('setup_413') . '</h3>
            <ul>' . $addonErr . '</ul>
            </li>
            </ul>';
        }

        // force to save config at this point
        // otherwise it would be saved in shutdown function and maybe would replace config changes made by db import in between
        Config::save();

        return $addonErr;
    }

    private static function reinstallPackages(): string
    {
        $error = '';
        Addon::initialize();
        AddonManager::synchronizeWithFileSystem();

        // enlist activated packages to ensure that all their classess are known in autoloader and can be referenced in other package's install.php
        foreach (Core::getPackageOrder() as $packageId) {
            Addon::require($packageId)->enlist();
        }
        foreach (Core::getPackageOrder() as $packageId) {
            $package = Addon::require($packageId);
            $manager = AddonManager::factory($package);

            if (!$manager->install()) {
                $error .= '<li>' . rex_escape($package->getPackageId()) . '<ul><li>' . $manager->getMessage() . '</li></ul></li>';
            }
        }

        if ($error) {
            $error = '<ul class="rex-ul1">
            <li>
            <h3 class="rex-hl3">' . I18n::msg('setup_413') . '</h3>
            <ul>' . $error . '</ul>
            </li>
            </ul>';
        }

        // force to save config at this point
        // otherwise it would be saved in shutdown function and maybe would replace config changes made by db import in between
        Config::save();

        return $error;
    }
}
