<?php

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_setup
{
    public const MIN_PHP_VERSION = REX_MIN_PHP_VERSION;
    public const MIN_MYSQL_VERSION = '5.5.3';

    /**
     * no-password placeholder required to support empty passwords/clearing the password.
     */
    public const DEFAULT_DUMMY_PASSWORD = '-REDAXO-DEFAULT-DUMMY-PASSWORD-';

    private static $MIN_PHP_EXTENSIONS = ['fileinfo', 'iconv', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer'];

    /**
     * very basic setup steps, so everything is in place for our browser-based setup wizard.
     *
     * @param string $skinAddon
     * @param string $skinPlugin
     */
    public static function init($skinAddon = 'be_style', $skinPlugin = 'redaxo')
    {
        // initial purge all generated files
        rex_delete_cache();

        // delete backend session
        rex_backend_login::deleteSession();

        // copy alle media files of the current rex-version into redaxo_media
        rex_dir::copy(rex_path::core('assets'), rex_path::coreAssets());

        // copy skins files/assets
        $skinAddon = rex_addon::get($skinAddon);
        $skinPlugin = $skinAddon->getPlugin($skinPlugin);
        rex_dir::copy($skinAddon->getPath('assets'), $skinAddon->getAssetsPath());
        rex_dir::copy($skinPlugin->getPath('assets'), $skinPlugin->getAssetsPath());
        if (is_file($skinAddon->getPath('install.php'))) {
            $skinAddon->includeFile('install.php');
        }
        if (is_file($skinPlugin->getPath('install.php'))) {
            $skinPlugin->includeFile('install.php');
        }
    }

    /**
     * checks environment related conditions.
     *
     * @return array An array of error messages
     */
    public static function checkEnvironment()
    {
        $errors = [];

        // -------------------------- VERSIONSCHECK
        if (1 == version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            $errors[] = rex_i18n::msg('setup_301', PHP_VERSION, self::MIN_PHP_VERSION);
        }

        // -------------------------- EXTENSION CHECK
        foreach (self::$MIN_PHP_EXTENSIONS as $extension) {
            if (!extension_loaded($extension)) {
                $errors[] = rex_i18n::msg('setup_302', $extension);
            }
        }

        return $errors;
    }

    /**
     * checks permissions of all required filesystem resources.
     *
     * @return array An array of error messages
     */
    public static function checkFilesystem()
    {
        // -------------------------- SCHREIBRECHTE
        $writables = [
            rex_path::media(),
            rex_path::assets(),
            rex_path::cache(),
            rex_path::data(),
            rex_path::src(),
        ];

        $getMod = static function ($path) {
            return substr(sprintf('%o', fileperms($path)), -3);
        };

        $func = static function ($dir) use (&$func, $getMod) {
            if (!rex_dir::isWritable($dir)) {
                return ['setup_304' => [$dir]];
            }
            $res = [];
            foreach (rex_finder::factory($dir) as $path => $file) {
                if ($file->isDir()) {
                    $res = array_merge_recursive($res, $func($path));
                } elseif (!$file->isWritable()) {
                    $res['setup_305'][] = $path;
                } elseif (0 !== strcasecmp(substr(PHP_OS, 0, 3), 'WIN') && '7' === substr($getMod($path), -1)) {
                    // check the "other" filesystem-bit for "all" permission.
                    $res['setup_311'][] = $path;
                }
            }
            return $res;
        };

        $res = [];
        foreach ($writables as $dir) {
            if (@is_dir($dir)) {
                $res = array_merge_recursive($res, $func($dir));
            } else {
                $res['setup_306'][] = $dir;
            }
        }

        return $res;
    }

    /**
     * Checks the version of the connected database server.
     * When validation of the database configs succeeds the settings will be used for rex_sql.
     *
     * @param array $config   array of database config
     * @param bool  $createDb Should the database be created, if it not exists
     *
     * @return string Error message
     */
    public static function checkDb($config, $createDb)
    {
        $err = rex_sql::checkDbConnection($config['db'][1]['host'], $config['db'][1]['login'], $config['db'][1]['password'], $config['db'][1]['name'], $createDb);
        if (true !== $err) {
            return $err;
        }

        // use given db config instead of saved config
        $orgDbConfig = rex::getProperty('db');
        try {
            rex::setProperty('db', $config['db']);
            $serverVersion = rex_sql::getServerVersion();
        } finally {
            rex::setProperty('db', $orgDbConfig);
        }

        if (1 == rex_version::compare($serverVersion, self::MIN_MYSQL_VERSION, '<')) {
            return rex_i18n::msg('sql_database_min_version', $serverVersion, self::MIN_MYSQL_VERSION);
        }

        return '';
    }

    /**
     * Basic php security checks. Returns a human readable strings on error.
     *
     * @return string[]
     */
    public static function checkPhpSecurity()
    {
        $security = [];

        if (PHP_SAPI !== 'cli' && !rex_request::isHttps()) {
            $security[] = rex_i18n::msg('setup_security_no_https');
        }

        if (function_exists('apache_get_modules') && in_array('mod_security', apache_get_modules())) {
            $security[] = rex_i18n::msg('setup_security_warn_mod_security');
        }

        if ('0' !== ini_get('session.auto_start')) {
            $security[] = rex_i18n::msg('setup_session_autostart_warning');
        }

        if (1 == version_compare(PHP_VERSION, '7.2', '<') && time() > strtotime('1 Dec 2019')) {
            $security[] = rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION);
        } elseif (1 == version_compare(PHP_VERSION, '7.3', '<') && time() > strtotime('30 Nov 2020')) {
            $security[] = rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION);
        } elseif (1 == version_compare(PHP_VERSION, '7.4', '<') && time() > strtotime('6 Dec 2021')) {
            $security[] = rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION);
        } elseif (1 == version_compare(PHP_VERSION, '8.0', '<') && time() > strtotime('28 Nov 2022')) {
            $security[] = rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION);
        }

        return $security;
    }

    /**
     * Basic database security checks. Returns a human readable strings on error.
     *
     * @return string[]
     */
    public static function checkDbSecurity()
    {
        $sql = rex_sql::factory();
        $dbVersion = $sql->getDbVersion();
        $dbType = $sql->getDbType();
        $security = [];

        if (rex_sql::MARIADB === $dbType) {
            // https://en.wikipedia.org/wiki/MariaDB#Versioning
            if (1 == version_compare($dbVersion, '5.2', '<') && time() > strtotime('1 Feb 2015')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '5.3', '<') && time() > strtotime('1 Nov 2015')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '5.5', '<') && time() > strtotime('1 Mar 2017')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.0', '<') && time() > strtotime('1 Apr 2020')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.1', '<') && time() > strtotime('1 Mar 2019')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.2', '<') && time() > strtotime('1 Oct 2020')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.3', '<') && time() > strtotime('1 May 2022')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.4', '<') && time() > strtotime('1 May 2023')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.5', '<') && time() > strtotime('1 Jun 2024')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            }
            // 10.5 is not yet released
        } elseif (rex_sql::MYSQL === $dbType) {
            // https://en.wikipedia.org/wiki/MySQL#Release_history
            if (1 == version_compare($dbVersion, '5.5', '<') && time() > strtotime('1 Dec 2013')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '5.6', '<') && time() > strtotime('1 Dec 2018')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '5.7', '<') && time() > strtotime('1 Feb 2021')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '8.0', '<') && time() > strtotime('1 Oct 2023')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            }
            // EOL 8.0 is April 2026
        }

        return $security;
    }
}
