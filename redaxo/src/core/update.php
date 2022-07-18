<?php

// don't use REX_MIN_PHP_VERSION or rex_setup::MIN_* constants here!
// while updating the core, the constants contain the old min versions from previous core version

if (PHP_VERSION_ID < 70300) {
    throw new rex_functional_exception(rex_i18n::msg(rex_string::versionCompare(rex::getVersion(), '5.14.0-dev', '<') ? 'setup_301' : 'setup_201', PHP_VERSION, '7.3'));
}

$minExtensions = ['ctype', 'fileinfo', 'filter', 'iconv', 'intl', 'mbstring', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer'];
$missing = array_filter($minExtensions, static function (string $extension) {
    return !extension_loaded($extension);
});
if ($missing) {
    throw new rex_functional_exception('Missing required php extension(s): '.implode(', ', $missing));
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
if (rex_string::versionCompare($dbVersion, $minVersion, '<')) {
    // The message was added in REDAXO 5.11.1, so it does not exist while updating from previous versions
    $message = rex_i18n::hasMsg('sql_database_required_version')
        ? rex_i18n::msg('sql_database_required_version', $dbType, $dbVersion, $minMysqlVersion, $minMariaDbVersion)
        : "The $dbType version $dbVersion is too old, you need at least MySQL $minMysqlVersion or MariaDB $minMariaDbVersion!";

    throw new rex_functional_exception($message);
}

// Since R5.7 we require at least R5.4 because of some `rex_sql_table` and `rex_sql::addRecord` usages in core addons
if (rex_string::versionCompare(rex::getVersion(), '5.6', '<')) {
    throw new rex_functional_exception(sprintf('The REDAXO version "%s" is too old for this update, please update to 5.6.5 before.', rex::getVersion()));
}

// Installer >= 2.9.2 required because of https://github.com/redaxo/redaxo/pull/4922
// (Installer < 2.9.0 also works, because it does not contain the bug)
$installerVersion = rex_addon::get('install')->getVersion();
if (rex_string::versionCompare($installerVersion, '2.9.2', '<') && rex_string::versionCompare($installerVersion, '2.9.0', '>=')) {
    throw new rex_functional_exception('This update requires at least version <b>2.9.2</b> of the <b>install</b> addon!');
}

$sessionKey = (string) rex::getProperty('instname').'_backend';

if (rex_string::versionCompare(rex::getVersion(), '5.7.0-beta3', '<')) {
    /** @psalm-suppress MixedArrayAssignment */
    $_SESSION[$sessionKey]['backend_login'] = $_SESSION[rex::getProperty('instname')]['backend_login'];
}

if (rex_string::versionCompare(rex::getVersion(), '5.9.0-beta1', '<')) {
    // do not use `rex_path::log()` because it does not exist while updating from rex < 5.9
    rex_dir::create(rex_path::data('log'));
    @rename(rex_path::coreData('system.log'), rex_path::data('log/system.log'));
    @rename(rex_path::coreData('system.log.2'), rex_path::data('log/system.log.2'));
}

if (rex_string::versionCompare(rex::getVersion(), '5.13.1', '<') && ($user = rex::getUser())) {
    /** @psalm-suppress MixedArrayAssignment */
    $_SESSION[$sessionKey]['backend_login']['password'] = $user->getValue('password');
}

$path = rex_path::coreData('config.yml');
$config = array_merge(
    rex_file::getConfig(__DIR__.'/default.config.yml'),
    rex_file::getConfig($path)
);

if (rex_string::versionCompare(rex::getVersion(), '5.12.0-dev', '<')) {
    $config['setup_addons'][] = 'install';
}

rex_file::putConfig($path, $config);

require __DIR__.'/install.php';
