<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_install_webservice
{
    public const HOST = 'www.redaxo.org';
    public const PORT = 443;
    public const SSL = true;
    public const PATH = '/de/ws/';
    public const REFRESH_CACHE = 600;

    /** @var array<string, array{stamp: int, data: array}> */
    private static $cache;

    /**
     * Retrieves the json-decoded content of the given path.
     *
     * @param string $path path to local cache-file
     *
     * @throws rex_functional_exception
     *
     * @return array
     */
    public static function getJson($path)
    {
        if (is_array($cache = self::getCache($path))) {
            return $cache;
        }
        $fullpath = self::PATH . self::getPath($path);

        $error = null;
        try {
            $socket = rex_socket::factory(self::HOST, self::PORT, self::SSL);
            $socket->setPath($fullpath);
            $response = $socket->doGet();
            if ($response->isOk()) {
                $data = json_decode($response->getBody(), true);
                if (isset($data['error']) && is_string($data['error'])) {
                    $error = rex_i18n::msg('install_webservice_error') . '<br />' . $data['error'];
                } elseif (is_array($data)) {
                    self::setCache($path, $data);
                    return $data;
                }
            }
        } catch (rex_socket_exception $e) {
            rex_logger::logException($e);
        }

        if (!$error) {
            $error = rex_i18n::msg('install_webservice_unreachable');
        }

        throw new rex_functional_exception($error);
    }

    /**
     * Download the content of the given url and make it available as a local file.
     *
     * @param string $url Url to a resource to download
     *
     * @throws rex_functional_exception
     *
     * @return string Returns a local path to the downloaded archive
     */
    public static function getArchive($url)
    {
        try {
            $socket = rex_socket::factoryUrl($url);
            $response = $socket->doGet();
            if ($response->isOk()) {
                $filename = rex_path::basename($url);
                $file = rex_path::addonCache('install', md5($filename) . '.' . rex_file::extension($filename));
                $response->writeBodyTo($file);
                return $file;
            }
        } catch (rex_socket_exception $e) {
            rex_logger::logException($e);
        }

        throw new rex_functional_exception(rex_i18n::msg('install_archive_unreachable'));
    }

    /**
     * POSTs the given data to the redaxo.org webservice.
     *
     * @param string      $path
     * @param string|null $archive Path to archive
     * @throws rex_functional_exception
     * @return void
     */
    public static function post($path, array $data, $archive = null)
    {
        $fullpath = self::PATH . self::getPath($path);
        $error = null;
        try {
            $socket = rex_socket::factory(self::HOST, self::PORT, self::SSL);
            $socket->setPath($fullpath);
            $files = [];
            if ($archive) {
                $files['archive']['path'] = $archive;
                $files['archive']['type'] = 'application/zip';
            }
            $response = $socket->doPost($data, $files);
            if ($response->isOk()) {
                $data = json_decode($response->getBody(), true);
                if (!isset($data['error']) || !is_string($data['error'])) {
                    return;
                }
                $error = rex_i18n::msg('install_webservice_error') . '<br />' . $data['error'];
            }
        } catch (rex_socket_exception $e) {
            rex_logger::logException($e);
        }

        if (!$error) {
            $error = rex_i18n::msg('install_webservice_unreachable');
        }

        throw new rex_functional_exception($error);
    }

    /**
     * Issues a http DELETE to the given path.
     *
     * @param string $path
     * @throws rex_functional_exception
     * @return void
     */
    public static function delete($path)
    {
        $fullpath = self::PATH . self::getPath($path);
        $error = null;
        try {
            $socket = rex_socket::factory(self::HOST, self::PORT, self::SSL);
            $socket->setPath($fullpath);
            $response = $socket->doDelete();
            if ($response->isOk()) {
                $data = json_decode($response->getBody(), true);
                if (!isset($data['error']) || !is_string($data['error'])) {
                    return;
                }
                $error = rex_i18n::msg('install_webservice_error') . '<br />' . $data['error'];
            }
        } catch (rex_socket_exception $e) {
            rex_logger::logException($e);
        }

        if (!$error) {
            $error = rex_i18n::msg('install_webservice_unreachable');
        }

        throw new rex_functional_exception($error);
    }

    /**
     * Appends api login credentials to the given path.
     *
     * @param string $path
     *
     * @return string
     */
    private static function getPath($path)
    {
        $path = !str_contains($path, '?') ? rtrim($path, '/') . '/?' : $path . '&';
        $path .= 'rex_version=' . rex::getVersion();

        static $config;
        if (null === $config) {
            $config = rex_file::getCache(rex_path::addonData('install', 'config.json'));
        }

        if (isset($config['api_login']) && $config['api_login'] && isset($config['api_key'])) {
            $path .= '&api_login=' . urlencode($config['api_login']) . '&api_key=' . urlencode($config['api_key']);
        }

        return $path;
    }

    /**
     * Deletes the local webservice cache.
     *
     * @param string|null $pathBegin
     * @return void
     */
    public static function deleteCache($pathBegin = null)
    {
        self::loadCache();
        if ($pathBegin) {
            foreach (self::$cache as $path => $_) {
                if (str_starts_with($path, $pathBegin)) {
                    unset(self::$cache[$path]);
                }
            }
        } else {
            self::$cache = [];
        }
        rex_file::putCache(rex_path::addonCache('install', 'webservice.cache'), self::$cache);
    }

    /**
     * Returns the content for the given path out of the local cache.
     *
     * @param string $path
     *
     * @return array|null
     */
    private static function getCache($path)
    {
        self::loadCache();
        if (isset(self::$cache[$path])) {
            return self::$cache[$path]['data'];
        }
        return null;
    }

    /**
     * Loads the local cached data into memory (only fresh data will be loaded).
     * @return void
     */
    private static function loadCache()
    {
        if (null === self::$cache) {
            /** @var array<string, array{stamp: int, data: array}> $cache */
            $cache = (array) rex_file::getCache(rex_path::addonCache('install', 'webservice.cache'));
            foreach ($cache as $path => $pathCache) {
                if ($pathCache['stamp'] > time() - self::REFRESH_CACHE) {
                    self::$cache[$path] = $pathCache;
                }
            }
        }
    }

    /**
     * Writes the given data into the local cache.
     *
     * @param string $path
     * @param array  $data
     * @return void
     */
    private static function setCache($path, $data)
    {
        self::$cache[$path]['stamp'] = time();
        self::$cache[$path]['data'] = $data;
        rex_file::putCache(rex_path::addonCache('install', 'webservice.cache'), self::$cache);
    }
}
