<?php

namespace Redaxo\Core;

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Database\Sql;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Http\Request;
use Redaxo\Core\Util\Version;
use rex_setup;
use rex_sql_exception;

use function extension_loaded;
use function ini_get;
use function is_bool;

use const PHP_OS;
use const PHP_SAPI;
use const PHP_VERSION;
use const STR_PAD_LEFT;

class SystemReport
{
    public const TITLE_REDAXO = 'REDAXO';
    public const TITLE_PACKAGES = 'Packages';
    public const TITLE_PHP = 'PHP';

    private function __construct() {}

    /**
     * @return self
     */
    public static function factory()
    {
        return new self();
    }

    /**
     * @return array<string, array<string, string|bool>>
     */
    public function get()
    {
        $data = [];

        $rexVersion = Core::getVersion();
        $hash = Version::gitHash(Path::base(), 'redaxo/redaxo');
        if ($hash) {
            $rexVersion .= '#' . $hash;
        }

        $data[self::TITLE_REDAXO] = [
            'Version' => $rexVersion,
        ];

        $data[self::TITLE_PHP] = [
            'Version' => PHP_VERSION,
            'OPcache' => extension_loaded('Zend OPcache') && ini_get('opcache.enable'),
            'Xdebug' => extension_loaded('xdebug'),
        ];

        $security = rex_setup::checkPhpSecurity();
        if ($security) {
            $data['PHP']['Warning'] = implode('<br/>', $security);
        }

        foreach (Core::getProperty('db') as $dbId => $db) {
            if (empty($db['name'])) {
                continue;
            }

            $dbData = [];

            try {
                $sql = Sql::factory($dbId);

                $dbData['Version'] = $sql->getDbType() . ' ' . $sql->getDbVersion();

                if (1 === $dbId) {
                    $security = rex_setup::checkDbSecurity();
                    if ($security) {
                        $dbData['Warning'] = implode('<br/>', $security);
                    }
                }
            } catch (rex_sql_exception $exception) {
                $dbData['Warning'] = $exception->getMessage();
            }

            if (1 === $dbId) {
                $data['Database'] = $dbData;
            } else {
                $data['Database ' . $dbId] = $dbData;
            }
        }

        $server = [
            'OS' => PHP_OS,
            'SAPI' => PHP_SAPI,
        ];

        if (isset($_SERVER['SERVER_SOFTWARE']) && preg_match('@^[^/]+(?:/[\d.]+)?@', $_SERVER['SERVER_SOFTWARE'], $match)) {
            $server['Webserver'] = $match[0];
        }

        $data['Server'] = $server;

        if ('cli' !== PHP_SAPI) {
            $data['Request'] = [
                'Browser' => $this->getBrowser(),
                'Protocol' => $_SERVER['SERVER_PROTOCOL'],
                'HTTPS' => Request::isHttps(),
            ];
        }

        $packages = [];
        foreach (Addon::getAvailableAddons() as $package) {
            $packages[$package->getPackageId()] = $package->getVersion();
        }

        $data[self::TITLE_PACKAGES] = $packages;

        return $data;
    }

    /**
     * @return string
     */
    public function asMarkdown()
    {
        $report = $this->get();

        $content = '';

        foreach ($report as $groupLabel => $group) {
            $rows = [];
            $labelWidth = max(13, mb_strlen($groupLabel));
            $valueWidth = 10;

            foreach ($group as $label => $value) {
                if (is_bool($value)) {
                    $value = $value ? 'yes' : 'no';
                }

                $rows[$label] = $value;
                $labelWidth = max($labelWidth, mb_strlen($label));
                $valueWidth = min(30, max($valueWidth, mb_strlen($value)));
            }

            $content .= '| ' . str_pad($groupLabel, $labelWidth) . ' | ' . str_repeat(' ', $valueWidth) . " |\n";
            $content .= '| ' . str_repeat('-', $labelWidth - 1) . ': | :' . str_repeat('-', $valueWidth - 1) . " |\n";

            foreach ($rows as $label => $value) {
                $content .= '| ' . str_pad($label, $labelWidth, ' ', STR_PAD_LEFT) . ' | ' . str_pad($value, $valueWidth) . " |\n";
            }

            $content .= "\n\n";
        }

        $content = rtrim($content);
        $database = isset($report['Database']['Version']) ? ', ' . (string) $report['Database']['Version'] : '';

        return <<<OUTPUT
            <details>
            <summary>System report (REDAXO {$report['REDAXO']['Version']}, PHP {$report['PHP']['Version']}{$database})</summary>

            $content

            </details>
            OUTPUT;
    }

    /**
     * @return string
     */
    private function getBrowser()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return '(unknown)';
        }

        $browser = $_SERVER['HTTP_USER_AGENT'];

        if (preg_match('@\bSeamonkey/\S+@i', $browser, $match)) {
            return $match[0];
        }
        if (preg_match('@\bFirefox/\S+@i', $browser, $match)) {
            return $match[0];
        }
        if (preg_match('@\b(?:OPR|Opera)/(\S+)@i', $browser, $match)) {
            return 'Opera/' . $match[1];
        }
        if (preg_match('@\bEdge/\S+@i', $browser, $match)) {
            return $match[0];
        }
        if (preg_match('@\bChromium/\S+@i', $browser, $match)) {
            return $match[0];
        }
        if (preg_match('@\bChrome/\S+@i', $browser, $match)) {
            return $match[0];
        }
        if (preg_match('@\bVersion/(\S+) Safari/\S+@i', $browser, $match)) {
            return 'Safari/' . $match[1];
        }
        if (preg_match('@\bMSIE/\S+@i', $browser, $match)) {
            return $match[0];
        }

        return $browser;
    }
}