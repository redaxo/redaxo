<?php

// don't use REX_MIN_PHP_VERSION or rex_setup::MIN_* constants here!
// while updating the core, the constants contain the old min versions from previous core version

// Since R6 we require at least R5.16 because of some `rex_sql_table` and `rex_sql::addRecord` usages in core addons
if (version_compare(rex::getVersion(), '5.16', '<')) {
    throw new rex_functional_exception(sprintf('The REDAXO version "%s" is too old for this update, please update to 5.16 before.', rex::getVersion()));
}

if (PHP_VERSION_ID < 80100) {
    throw new rex_functional_exception(rex_i18n::msg('setup_201', PHP_VERSION, '8.1'));
}

$minExtensions = ['ctype', 'fileinfo', 'filter', 'iconv', 'intl', 'mbstring', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer'];
$missing = array_filter($minExtensions, static function (string $extension) {
    return !extension_loaded($extension);
});
if ($missing) {
    throw new rex_functional_exception('Missing required php extension(s): ' . implode(', ', $missing));
}

$minMysqlVersion = '5.6';
$minMariaDbVersion = '10.1';

$minVersion = $minMysqlVersion;
$dbType = 'MySQL';
$dbVersion = rex_sql::getServerVersion();
if (preg_match('/^(?:\d+\.\d+\.\d+-)?(\d+\.\d+\.\d+)-mariadb/i', $dbVersion, $match)) {
    $minVersion = $minMariaDbVersion;
    $dbType = 'MariaDB';
    $dbVersion = $match[1];
}
if (rex_version::compare($dbVersion, $minVersion, '<')) {
    // The message was added in REDAXO 5.11.1, so it does not exist while updating from previous versions
    $message = rex_i18n::hasMsg('sql_database_required_version')
        ? rex_i18n::msg('sql_database_required_version', $dbType, $dbVersion, $minMysqlVersion, $minMariaDbVersion)
        : "The $dbType version $dbVersion is too old, you need at least MySQL $minMysqlVersion or MariaDB $minMariaDbVersion!";

    throw new rex_functional_exception($message);
}

$path = rex_path::coreData('config.yml');
$config = array_merge(
    rex_file::getConfig(__DIR__ . '/default.config.yml'),
    rex_file::getConfig($path),
);

rex_file::putConfig($path, $config);

require __DIR__ . '/install.php';
