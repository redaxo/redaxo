<?php

/**
 * @package redaxo\core
 */
class rex_system_report
{
    private function __construct()
    {
    }

    public static function factory()
    {
        return new self();
    }

    public function get()
    {
        $data = [];

        $data['REDAXO'] = [
            'Version' => rex::getVersion(),
        ];

        $data['PHP'] = [
            'Version' => PHP_VERSION,
            'OPcache' => extension_loaded('Zend OPcache') && ini_get('opcache.enable'),
            'APCu' => extension_loaded('apcu') && ini_get('apc.enabled'),
            'Xdebug' => extension_loaded('xdebug'),
        ];

        $dbCharacterSet = rex_sql::factory()->getArray(
            'SELECT default_character_set_name, default_collation_name FROM information_schema.SCHEMATA WHERE schema_name = ?',
            [rex::getProperty('db')[1]['name']]
        )[0];

        $data['Database'] = [
            'Version' => rex_sql::getServerVersion(),
            'Character set' => "$dbCharacterSet[default_character_set_name] ($dbCharacterSet[default_collation_name])",
        ];

        return $data;
    }
}
