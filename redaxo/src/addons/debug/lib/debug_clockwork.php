<?php
/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_debug_clockwork
{
    /** @var \Clockwork\Support\Vanilla\Clockwork|null */
    private static $instance;

    /**
     * @psalm-assert \Clockwork\Support\Vanilla\Clockwork self::$instance
     */
    private static function init(): void
    {
        $clockwork = \Clockwork\Support\Vanilla\Clockwork::init([
            'storage_files_path' => self::getStoragePath(),
            'storage_files_compress' => true,

            // there is a probability from 1 to 100 that the cleanup mechanism will be triggered and files older than 2 days will be removed
            'storage_expiration' => 60 * 24 * 2,
        ]);
        if (extension_loaded('xdebug')) {
            $clockwork->getClockwork()->addDataSource(new \Clockwork\DataSource\XdebugDataSource());
        }

        self::$instance = $clockwork;
    }

    public static function getInstance(): Clockwork\Clockwork
    {
        return self::getHelper()->getClockwork();
    }

    public static function getHelper(): Clockwork\Support\Vanilla\Clockwork
    {
        if (!self::$instance) {
            self::init();
        }
        return self::$instance;
    }

    public static function getFullClockworkApiUrl(): string
    {
        $https = isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS'];
        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'] ?? null;
        $uri = dirname($_SERVER['REQUEST_URI']).'/'.self::getClockworkApiUrl();

        $scheme = $https ? 'https' : 'http';
        $port = (!$https && 80 != $port || $https && 443 != $port) ? ":{$port}" : '';

        return "{$scheme}://{$host}{$port}{$uri}";
    }

    public static function getClockworkApiUrl(): string
    {
        return rex_url::backendPage('debug', rex_api_debug::getUrlParams(), false);
    }

    /**
     * @return void
     */
    public static function ensureStoragePath()
    {
        $storagePath = self::getStoragePath();
        if (!is_dir($storagePath)) {
            rex_dir::create($storagePath);
        }
    }

    /**
     * @return string
     */
    public static function getStoragePath()
    {
        return rex_addon::get('debug')->getCachePath('clockwork.db');
    }

    /**
     * We cannot rely on rex::isDebugMode() because it is always true on the console.
     * So we have to check the config file itself.
     */
    public static function isRexDebugEnabled(): bool
    {
        if (PHP_SAPI !== 'cli') {
            return rex::isDebugMode();
        }

        $coreConfigCacheFile = rex_path::coreCache('config.yml.cache');
        $coreConfigCache = rex_file::getCache($coreConfigCacheFile);
        /** @var bool $debugEnabled */
        $debugEnabled = $coreConfigCache['debug']['enabled'] ?? false;

        return $debugEnabled;
    }
}
