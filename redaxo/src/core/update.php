<?php

use Redaxo\Core\Core;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;

// don't use REX_MIN_PHP_VERSION or rex_setup::MIN_* constants here!
// while updating the core, the constants contain the old min versions from previous core version

if (version_compare(Core::getVersion(), '5.16', '<')) {
    throw new rex_functional_exception(sprintf('The REDAXO version "%s" is too old for this update, please update to 5.16 before.', Core::getVersion()));
}

if (PHP_VERSION_ID < 80300) {
    throw new rex_functional_exception(I18n::msg('setup_201', PHP_VERSION, '8.3'));
}

$minExtensions = ['ctype', 'fileinfo', 'filter', 'iconv', 'intl', 'mbstring', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer'];
$missing = array_filter($minExtensions, static function (string $extension) {
    return !extension_loaded($extension);
});
if ($missing) {
    throw new rex_functional_exception('Missing required php extension(s): ' . implode(', ', $missing));
}

$minMysqlVersion = '8.0';
$minMariaDbVersion = '10.4';

$minVersion = $minMysqlVersion;
$dbType = 'MySQL';
$dbVersion = Sql::getServerVersion();
if (preg_match('/^(?:\d+\.\d+\.\d+-)?(\d+\.\d+\.\d+)-mariadb/i', $dbVersion, $match)) {
    $minVersion = $minMariaDbVersion;
    $dbType = 'MariaDB';
    $dbVersion = $match[1];
}
if (rex_version::compare($dbVersion, $minVersion, '<')) {
    // The message was added in REDAXO 5.11.1, so it does not exist while updating from previous versions
    $message = I18n::hasMsg('sql_database_required_version')
        ? I18n::msg('sql_database_required_version', $dbType, $dbVersion, $minMysqlVersion, $minMariaDbVersion)
        : "The $dbType version $dbVersion is too old, you need at least MySQL $minMysqlVersion or MariaDB $minMariaDbVersion!";

    throw new rex_functional_exception($message);
}

$path = Path::coreData('config.yml');
$config = array_merge(
    rex_file::getConfig(__DIR__ . '/default.config.yml'),
    rex_file::getConfig($path),
);

rex_file::putConfig($path, $config);

require __DIR__ . '/install.php';
