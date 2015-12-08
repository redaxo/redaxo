<?php

/**
 * Abstract base class for packages.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
abstract class rex_package implements rex_package_interface
{
    const FILE_PACKAGE = 'package.yml';
    const FILE_BOOT = 'boot.php';
    const FILE_INSTALL = 'install.php';
    const FILE_INSTALL_SQL = 'install.sql';
    const FILE_UNINSTALL = 'uninstall.php';
    const FILE_UNINSTALL_SQL = 'uninstall.sql';
    const FILE_UPDATE = 'update.php';

    /**
     * Name of the package.
     *
     * @var string
     */
    private $name;

    /**
     * Properties.
     *
     * @var array
     */
    private $properties = [];

    /**
     * Flag whether the properties of package.yml are loaded.
     *
     * @var bool
     */
    private $propertiesLoaded = false;

    /**
     * Constructor.
     *
     * @param string $name Name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the package (addon or plugin) by the given package id.
     *
     * @param string $packageId Package ID
     *
     * @throws InvalidArgumentException
     *
     * @return self
     */
    public static function get($packageId)
    {
        if (!is_string($packageId)) {
            throw new InvalidArgumentException('Expecting $packageId to be string, but ' . gettype($packageId) . ' given!');
        }
        $package = explode('/', $packageId);
        $addon = rex_addon::get($package[0]);
        if (isset($package[1])) {
            return $addon->getPlugin($package[1]);
        }
        return $addon;
    }

    /**
     * Returns if the package exists.
     *
     * @param string $packageId Package ID
     *
     * @return bool
     */
    public static function exists($packageId)
    {
        $package = explode('/', $packageId);
        if (isset($package[1])) {
            return rex_plugin::exists($package[0], $package[1]);
        }
        return rex_addon::exists($package[0]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfig($key, $value = null)
    {
        return rex_config::set($this->getPackageId(), $key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($key = null, $default = null)
    {
        return rex_config::get($this->getPackageId(), $key, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function hasConfig($key = null)
    {
        return rex_config::has($this->getPackageId(), $key);
    }

    /**
     * {@inheritdoc}
     */
    public function removeConfig($key)
    {
        return rex_config::remove($this->getPackageId(), $key);
    }

    /**
     * {@inheritdoc}
     */
    public function setProperty($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        $this->properties[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty($key, $default = null)
    {
        if ($this->hasProperty($key)) {
            return $this->properties[$key];
        }
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function hasProperty($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        if (!isset($this->properties[$key]) && !$this->propertiesLoaded) {
            $this->loadProperties();
        }
        return isset($this->properties[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function removeProperty($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        unset($this->properties[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable()
    {
        return $this->isInstalled() && (boolean) $this->getProperty('status', false);
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return (boolean) $this->getProperty('install', false);
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor($default = null)
    {
        return $this->getProperty('author', $default);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion($format = null)
    {
        $version = $this->getProperty('version');
        if ($format) {
            return rex_formatter::version($version, $format);
        }
        return $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportPage($default = null)
    {
        return $this->getProperty('supportpage', $default);
    }

    /**
     * {@inheritdoc}
     */
    public function includeFile($file)
    {
        if (file_exists($this->getPath($file))) {
            include $this->getPath($file);
        } else {
            include $file;
        }
    }

    /**
     * Loads the properties of package.yml.
     */
    private function loadProperties()
    {
        static $cache = null;
        if (is_null($cache)) {
            $cache = rex_file::getCache(rex_path::coreCache('packages.cache'));
        }
        $id = $this->getPackageId();
        $file = $this->getPath(self::FILE_PACKAGE);
        if (!file_exists($file)) {
            $this->propertiesLoaded = true;
            return;
        }
        if (
            isset($cache[$id]) &&
            (!rex::isBackend() || !($user = rex::getUser()) || !$user->isAdmin() || $cache[$id]['timestamp'] >= filemtime($file))
        ) {
            $properties = $cache[$id]['data'];
        } else {
            $properties = rex_file::getConfig($file);
            $cache[$id]['timestamp'] = filemtime($file);
            $cache[$id]['data'] = $properties;

            static $registeredShutdown = false;
            if (!$registeredShutdown) {
                $registeredShutdown = true;
                register_shutdown_function(function () use (&$cache) {
                    foreach ($cache as $package => $_) {
                        if (!rex_package::exists($package)) {
                            unset($cache[$package]);
                        }
                    }
                    rex_file::putCache(rex_path::coreCache('packages.cache'), $cache);
                });
            }
        }
        foreach ($properties as $key => $value) {
            if (!isset($this->properties[$key])) {
                $this->properties[$key] = rex_i18n::translateArray($value, false, [$this, 'i18n']);
            }
        }
        $this->propertiesLoaded = true;
    }

    /**
     * Returns the registered packages.
     *
     * @return self[]
     */
    public static function getRegisteredPackages()
    {
        return self::getPackages('Registered');
    }

    /**
     * Returns the installed packages.
     *
     * @return self[]
     */
    public static function getInstalledPackages()
    {
        return self::getPackages('Installed');
    }

    /**
     * Returns the available packages.
     *
     * @return self[]
     */
    public static function getAvailablePackages()
    {
        return self::getPackages('Available');
    }

    /**
     * Returns the setup packages.
     *
     * @return self[]
     */
    public static function getSetupPackages()
    {
        return self::getPackages('Setup', 'System');
    }

    /**
     * Returns the system packages.
     *
     * @return self[]
     */
    public static function getSystemPackages()
    {
        return self::getPackages('System');
    }

    /**
     * Returns the packages by the given method.
     *
     * @param string $method       Method
     * @param string $pluginMethod Optional other method for plugins
     *
     * @return self[]
     */
    private static function getPackages($method, $pluginMethod = null)
    {
        $packages = [];
        $addonMethod = 'get' . $method . 'Addons';
        $pluginMethod = 'get' . ($pluginMethod ?: $method) . 'Plugins';
        foreach (rex_addon::$addonMethod() as $addon) {
            $packages[$addon->getPackageId()] = $addon;
            foreach ($addon->$pluginMethod() as $plugin) {
                $packages[$plugin->getPackageId()] = $plugin;
            }
        }
        return $packages;
    }
}
