<?php

/**
 * @package redaxo\install
 *
 * @internal
 */
class rex_install_webservice
{
    const HOST = 'www.redaxo.org';
    const PORT = 443;
    const SSL = true;
    const PATH = '/de/ws/';
    const REFRESH_CACHE = 600;

    private static $cache;

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

    public static function getArchive($url)
    {
        try {
            $socket = rex_socket::factoryUrl($url);
            $response = $socket->doGet();
            if ($response->isOk()) {
                $filename = basename($url);
                $file = rex_path::addonCache('install', md5($filename) . '.' . rex_file::extension($filename));
                $response->writeBodyTo($file);
                return $file;
            }
        } catch (rex_socket_exception $e) {
            rex_logger::logException($e);
        }

        throw new rex_functional_exception(rex_i18n::msg('install_archive_unreachable'));
    }

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

    private static function getPath($path)
    {
        $path = strpos($path, '?') === false ? rtrim($path, '/') . '/?' : $path . '&';
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

    public static function deleteCache($pathBegin = null)
    {
        self::loadCache();
        if ($pathBegin) {
            foreach (self::$cache as $path => $cache) {
                if (strpos($path, $pathBegin) === 0) {
                    unset(self::$cache[$path]);
                }
            }
        } else {
            self::$cache = [];
        }
        rex_file::putCache(rex_path::addonCache('install', 'webservice.cache'), self::$cache);
    }

    private static function getCache($path)
    {
        self::loadCache();
        if (isset(self::$cache[$path])) {
            return self::$cache[$path]['data'];
        }
        return null;
    }

    private static function loadCache()
    {
        if (self::$cache === null) {
            foreach ((array) rex_file::getCache(rex_path::addonCache('install', 'webservice.cache')) as $path => $cache) {
                if ($cache['stamp'] > time() - self::REFRESH_CACHE) {
                    self::$cache[$path] = $cache;
                }
            }
        }
    }

    private static function setCache($path, $data)
    {
        self::$cache[$path]['stamp'] = time();
        self::$cache[$path]['data'] = $data;
        rex_file::putCache(rex_path::addonCache('install', 'webservice.cache'), self::$cache);
    }
}
