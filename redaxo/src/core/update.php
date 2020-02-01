<?php

// don't use REX_MIN_PHP_VERSION or rex_setup::MIN_MYSQL_VERSION here!
// while updating the core, the constants contain the old min versions from previous core version

if (PHP_VERSION_ID < 70103) {
    throw new rex_functional_exception(rex_i18n::msg('setup_301', PHP_VERSION, '7.1.3'));
}

$mysqlVersion = rex_sql::getServerVersion();
$minMysqlVersion = '5.5.3';
if (rex_string::versionCompare($mysqlVersion, $minMysqlVersion, '<')) {
    // The message was added in REDAXO 5.6.0, so it does not exist while updating from previous versions
    $message = rex_i18n::hasMsg('sql_database_min_version')
        ? rex_i18n::msg('sql_database_min_version', $mysqlVersion, $minMysqlVersion)
        : "The MySQL version $mysqlVersion is too old, you need at least version $minMysqlVersion!";

    throw new rex_functional_exception($message);
}

// Since R5.7 we require at least R5.4 because of some `rex_sql_table` and `rex_sql::addRecord` usages in core addons
if (rex_string::versionCompare(rex::getVersion(), '5.6', '<')) {
    throw new rex_functional_exception(sprintf('The REDAXO version "%s" is too old for this update, please update to 5.6.5 before.', rex::getVersion()));
}

if (rex_string::versionCompare(rex::getVersion(), '5.7.0-beta3', '<')) {
    $_SESSION[rex::getProperty('instname').'_backend']['backend_login'] = $_SESSION[rex::getProperty('instname')]['backend_login'];
}

if (rex_string::versionCompare(rex::getVersion(), '5.9.0-beta1', '<')) {
    // do not use `rex_path::log()` because it does not exist while updating from rex < 5.9
    rex_dir::create(rex_path::data('log'));
    @rename(rex_path::coreData('system.log'), rex_path::data('log/system.log'));
    @rename(rex_path::coreData('system.log.2'), rex_path::data('log/system.log.2'));
}

$path = rex_path::coreData('config.yml');
rex_file::putConfig($path, array_merge(
    rex_file::getConfig(__DIR__.'/default.config.yml'),
    rex_file::getConfig($path)
));

require __DIR__.'/install.php';
