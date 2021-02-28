<?php

/**
 * @package redaxo\core
 *
 * @internal
 */
class rex_setup
{
    // These values must be synchronized with the values in redaxo/src/core/update.php
    public const MIN_PHP_VERSION = REX_MIN_PHP_VERSION;
    public const MIN_MYSQL_VERSION = '5.6';
    public const MIN_MARIADB_VERSION = '10.1';

    /**
     * no-password placeholder required to support empty passwords/clearing the password.
     */
    public const DEFAULT_DUMMY_PASSWORD = '-REDAXO-DEFAULT-DUMMY-PASSWORD-';

    public const DB_MODE_SETUP_NO_OVERRIDE = 0;
    public const DB_MODE_SETUP_AND_OVERRIDE = 1;
    public const DB_MODE_SETUP_SKIP = 2;
    public const DB_MODE_SETUP_IMPORT_BACKUP = 3;
    public const DB_MODE_SETUP_UPDATE_FROM_PREVIOUS = 4;

    private const MIN_PHP_EXTENSIONS = ['fileinfo', 'filter', 'iconv', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer'];

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

        // copy alle media files of the current rex-version into redaxo_media
        rex_dir::copy(rex_path::core('assets'), rex_path::coreAssets());
        // in a regular release the folder will never be empty, because we ship it prefilled.
        // provide a error message for 'git cloned' sources, to give newcomers a hint why the very first setup might look broken.
        // we intentionally dont check permissions here, as those will be checked in a later setup step.
        if (!is_dir(rex_path::coreAssets())) {
            throw new rex_exception('Unable to copy assets to "'. rex_path::coreAssets() .'". Is the folder writable for the webserver?');
        }

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
        foreach (self::MIN_PHP_EXTENSIONS as $extension) {
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
            $mod = substr(sprintf('%o', fileperms($path)), -3);
            assert(is_string($mod));
            return $mod;
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
            $sql = rex_sql::factory();
            $type = $sql->getDbType();
            $version = $sql->getDbVersion();
        } finally {
            rex::setProperty('db', $orgDbConfig);
        }

        $minVersion = rex_sql::MARIADB === $type ? self::MIN_MARIADB_VERSION : self::MIN_MYSQL_VERSION;
        if (rex_version::compare($version, $minVersion, '<')) {
            return rex_i18n::msg('sql_database_required_version', $type, $version, self::MIN_MYSQL_VERSION, self::MIN_MARIADB_VERSION);
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

        if (ini_get('session.auto_start')) {
            $security[] = rex_i18n::msg('setup_session_autostart_warning');
        }

        // https://www.php.net/supported-versions.php
        if (1 == version_compare(PHP_VERSION, '7.4', '<') && time() > strtotime('6 Dec 2021')) {
            $security[] = rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION);
        } elseif (1 == version_compare(PHP_VERSION, '8.0', '<') && time() > strtotime('28 Nov 2022')) {
            $security[] = rex_i18n::msg('setup_security_deprecated_php', PHP_VERSION);
        } elseif (1 == version_compare(PHP_VERSION, '8.1', '<') && time() > strtotime('26 Nov 2023')) {
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
            if (1 == version_compare($dbVersion, '10.2', '<') && time() > strtotime('1 Oct 2020')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.3', '<') && time() > strtotime('1 May 2022')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.4', '<') && time() > strtotime('1 May 2023')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.5', '<') && time() > strtotime('1 Jun 2024')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '10.6', '<') && time() > strtotime('1 Jun 2025')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mariadb', $dbVersion);
            }
        } elseif (rex_sql::MYSQL === $dbType) {
            // https://en.wikipedia.org/wiki/MySQL#Release_history
            if (1 == version_compare($dbVersion, '5.7', '<') && time() > strtotime('1 Feb 2021')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '8.0', '<') && time() > strtotime('1 Oct 2023')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            } elseif (1 == version_compare($dbVersion, '8.1', '<') && time() > strtotime('1 Apr 2026')) {
                $security[] = rex_i18n::msg('setup_security_deprecated_mysql', $dbVersion);
            }
        }

        return $security;
    }

    /**
     * Returns true when we are running the very first setup for this instance.
     * Otherwise false is returned, e.g. when setup was re-started from the core/systems page.
     */
    public static function isInitialSetup(): bool
    {
        try {
            $userSql = rex_sql::factory();
            $userSql->setQuery('select * from ' . rex::getTable('user') . ' LIMIT 1');

            return 0 == $userSql->getRows();
        } catch (rex_sql_could_not_connect_exception $e) {
            return true;
        } catch (rex_sql_exception $e) {
            $sql = $e->getSql();
            if ($sql && rex_sql::ERRNO_TABLE_OR_VIEW_DOESNT_EXIST === $sql->getErrno()) {
                return true;
            }
            throw $e;
        }
    }

    /**
     * @return string|false Single-User-Setup URL or `false` on failure
     */
    public static function startWithToken()
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        $configFile = rex_path::coreData('config.yml');
        $config = rex_file::getConfig($configFile);

        $config['setup'] = isset($config['setup']) && is_array($config['setup']) ? $config['setup'] : [];
        $config['setup'][$token] = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

        if (false === rex_file::putConfig($configFile, $config)) {
            return false;
        }

        return rex_url::backendPage('setup', ['setup_token' => $token], false);
    }

    public static function isEnabled(): bool
    {
        $setup = rex::getProperty('setup', false);

        if (!is_array($setup)) {
            // system wide setup
            return (bool) $setup;
        }

        $currentToken = self::getToken();

        if (!$currentToken && rex::isFrontend()) {
            // no token in url, fast fail in frontend
            // (in backend all existing tokens are revalidated below)
            return false;
        }

        // invalidate expired tokens
        $updated = false;
        foreach ($setup as $token => $expire) {
            if (strtotime($expire) < time()) {
                unset($setup[$token]);
                $updated = true;
            }
        }

        if ($updated) {
            $configFile = rex_path::coreData('config.yml');
            $config = rex_file::getConfig($configFile);
            $config['setup'] = $setup ?: false;
            rex_file::putConfig($configFile, $config);
        }

        return isset($setup[$currentToken]);
    }

    public static function getContext(): rex_context
    {
        $context = new rex_context([
            'page' => 'setup',
            'lang' => rex_request('lang', 'string', ''),
            'step' => rex_request('step', 'int', 1),
        ]);

        if ($token = self::getToken()) {
            $context->setParam('setup_token', $token);
        }

        return $context;
    }

    /**
     * Mark the setup as completed.
     */
    public static function markSetupCompleted(): bool
    {
        $configFile = rex_path::coreData('config.yml');
        $config = array_merge(
            rex_file::getConfig(rex_path::core('default.config.yml')),
            rex_file::getConfig($configFile)
        );

        if (is_array($config['setup'])) {
            // remove current token
            if ($token = self::getToken()) {
                unset($config['setup'][$token]);
            }

            // if array is empty now, convert it to global `false` value
            $config['setup'] = $config['setup'] ?: false;
        } else {
            $config['setup'] = false;
        }

        $configWritten = rex_file::putConfig($configFile, $config);

        if ($configWritten) {
            rex_file::delete(rex_path::coreCache('config.yml.cache'));
        }

        return $configWritten;
    }

    private static function getToken(): ?string
    {
        return rex_get('setup_token', 'string', null);
    }
}
