<?php

namespace Redaxo\Core\Setup;

use Redaxo\Core\Database\ConnectionConfig;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\Finder;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Type;
use Redaxo\Core\Util\Version;

use function sprintf;

use const PHP_OS;

/**
 * @internal
 */
final class Setup
{
    public const MIN_MYSQL_VERSION = '8.4';
    public const MIN_MARIADB_VERSION = '10.11';

    /** no-password placeholder required to support empty passwords/clearing the password. */
    public const DEFAULT_DUMMY_PASSWORD = '-REDAXO-DEFAULT-DUMMY-PASSWORD-';

    private function __construct() {}

    /**
     * checks permissions of all required filesystem resources.
     *
     * @return array<string, non-empty-list<string>> An array of error messages
     */
    public static function checkFilesystem(): array
    {
        // -------------------------- SCHREIBRECHTE
        $writables = [
            Path::media(),
            Path::assets(),
            Path::cache(),
            Path::data(),
        ];

        $getMod = static function ($path) {
            return Type::string(substr(sprintf('%o', fileperms($path)), -3));
        };

        $func = static function (string $dir) use (&$func, $getMod) {
            if (!Dir::isWritable($dir)) {
                return ['setup_204' => [$dir]];
            }
            $res = [];
            foreach (Finder::factory($dir) as $path => $file) {
                if ($file->isDir()) {
                    $res = array_merge_recursive($res, $func($path));
                } elseif (!$file->isWritable()) {
                    $res['setup_205'][] = $path;
                } elseif (0 !== strcasecmp(substr(PHP_OS, 0, 3), 'WIN') && str_ends_with($getMod($path), '7')) {
                    // check the "other" filesystem-bit for "all" permission.
                    $res['setup_211'][] = $path;
                }
            }
            return $res;
        };

        $res = [];
        foreach ($writables as $dir) {
            @Dir::create($dir);
            if (@is_dir($dir)) {
                $res = array_merge_recursive($res, $func($dir));
            } else {
                $res['setup_206'][] = $dir;
            }
        }

        return $res;
    }

    /**
     * Checks the version of the connected database server.
     * @param bool $createDb Should the database be created, if it not exists
     *
     * @return string Error message
     */
    public static function checkDb(bool $createDb): string
    {
        $dbConfig = ConnectionConfig::get();

        $err = Sql::checkDbConnection($dbConfig->host, $dbConfig->login, $dbConfig->password, $dbConfig->name, $createDb, Sql::createSslOptions($dbConfig));

        if (true !== $err) {
            return $err;
        }

        return self::checkDbVersion();
    }

    /**
     * Checks that the connected database server meets the minimum version.
     *
     * @return string Error message, empty if the version is supported
     */
    public static function checkDbVersion(): string
    {
        $sql = Sql::factory();
        $type = $sql->getDbType();
        $version = $sql->getDbVersion();

        $minVersion = Sql::MARIADB === $type ? self::MIN_MARIADB_VERSION : self::MIN_MYSQL_VERSION;
        if (Version::compare($version, $minVersion, '<')) {
            return I18n::msg('sql_database_required_version', $type, $version, self::MIN_MYSQL_VERSION, self::MIN_MARIADB_VERSION);
        }

        return '';
    }
}
