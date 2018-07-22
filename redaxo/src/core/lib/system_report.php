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
            'Xdebug' => extension_loaded('xdebug'),
        ];

        foreach (rex::getProperty('db') as $dbId => $db) {
            if (empty($db['name'])) {
                continue;
            }

            $dbCharacterSet = rex_sql::factory($dbId)->getArray(
                'SELECT default_character_set_name, default_collation_name FROM information_schema.SCHEMATA WHERE schema_name = ?',
                [$db['name']]
            )[0];

            $data['Database'.(1 === $dbId ? '' : " $dbId")] = [
                'Version' => rex_sql::getServerVersion(),
                'Character set' => "$dbCharacterSet[default_character_set_name] ($dbCharacterSet[default_collation_name])",
            ];
        }

        $server = [
            'OS' => PHP_OS_FAMILY,
            'SAPI' => PHP_SAPI,
        ];

        if (isset($_SERVER['SERVER_SOFTWARE']) && preg_match('@^[^/]+(?:/[\d.]+)?@', $_SERVER['SERVER_SOFTWARE'], $match)) {
            $server['Webserver'] = $match[0];
        }
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            $server['Protocol'] = $_SERVER['SERVER_PROTOCOL'];
        }
        if ('cli' !== PHP_SAPI) {
            $server['HTTPS'] = rex_request::isHttps();
        }

        $data['Server'] = $server;

        $packages = [];
        foreach (rex_package::getAvailablePackages() as $package) {
            $packages[$package->getPackageId()] = $package->getVersion();
        }

        $data['Packages'] = $packages;

        return $data;
    }

    public function asMarkdown()
    {
        $report = $this->get();

        $content = '';

        foreach ($report as $groupLabel => $group) {
            $rows = [];
            $labelWidth = mb_strlen($groupLabel);
            $valueWidth = 0;

            foreach ($group as $label => $value) {
                if (is_bool($value)) {
                    $value = $value ? 'yes' : 'no';
                }

                $rows[$label] = $value;
                $labelWidth = max($labelWidth, mb_strlen($label));
                $valueWidth = max($valueWidth, mb_strlen($value));
            }

            $content .= '| '.str_pad($groupLabel, $labelWidth).' | '.str_repeat(' ', $valueWidth)." |\n";
            $content .= '| '.str_repeat('-', $labelWidth - 1).': | '.str_repeat('-', $valueWidth)." |\n";

            foreach ($rows as $label => $value) {
                $content .= '| '.str_pad($label, $labelWidth, ' ', STR_PAD_LEFT).' | '.str_pad($value, $valueWidth)." |\n";
            }

            $content .= "\n\n";
        }

        $content = rtrim($content);

        return <<<OUTPUT
<details>
<summary>System report (REDAXO {$report['REDAXO']['Version']}, PHP {$report['PHP']['Version']})</summary>

$content
</details>
OUTPUT;
    }
}
