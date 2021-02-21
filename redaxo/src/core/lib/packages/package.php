<?php

/**
 * Abstract base class for packages.
 *
 * @author gharlan
 *
 * @package redaxo\core\packages
 */
abstract class rex_package implements rex_package_interface
{
    public const FILE_PACKAGE = 'package.yml';
    public const FILE_BOOT = 'boot.php';
    public const FILE_INSTALL = 'install.php';
    public const FILE_INSTALL_SQL = 'install.sql';
    public const FILE_UNINSTALL = 'uninstall.php';
    public const FILE_UNINSTALL_SQL = 'uninstall.sql';
    public const FILE_UPDATE = 'update.php';

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
     * @return rex_package_interface If the package exists, a `rex_package` is returned, otherwise a `rex_null_package`
     */
    public static function get($packageId)
    {
        if (!is_string($packageId)) {
            throw new InvalidArgumentException('Expecting $packageId to be string, but ' . gettype($packageId) . ' given!');
        }

        [$addonId, $pluginId] = self::splitId($packageId);
        $addon = rex_addon::get($addonId);

        if ($pluginId) {
            return $addon->getPlugin($pluginId);
        }

        return $addon;
    }

    /**
     * Returns the package (addon or plugin) by the given package id.
     *
     * @throws RuntimeException if the package does not exist
     */
    public static function require(string $packageId): self
    {
        [$addonId, $pluginId] = self::splitId($packageId);
        $addon = rex_addon::require($addonId);

        if ($pluginId) {
            return $addon->requirePlugin($pluginId);
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
        [$addonId, $pluginId] = self::splitId($packageId);

        if ($pluginId) {
            return rex_plugin::exists($addonId, $pluginId);
        }

        return rex_addon::exists($addonId);
    }

    /**
     * Splits the package id into a tuple of addon id and plugin id (if existing).
     *
     * @return array{string, ?string}
     */
    public static function splitId(string $packageId): array
    {
        $parts = explode('/', $packageId, 2);
        $parts[1] = $parts[1] ?? null;

        return $parts;
    }

    /**
     * @return string
     */
    abstract public function getPackageId();

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
        return $this->isInstalled() && (bool) $this->getProperty('status', false);
    }

    /**
     * {@inheritdoc}
     */
    public function isInstalled()
    {
        return (bool) $this->getProperty('install', false);
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
     *
     * @noRector
     */
    public function includeFile($file, array $context = [])
    {
        /** @noRector \Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector */
        $__file = $file;
        /** @noRector \Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector */
        $__context = $context;

        unset($file, $context);

        /** @noRector \Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector */
        extract($__context, EXTR_SKIP);

        /** @noRector \Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector */
        if (is_file($this->getPath($__file))) {
            /** @noRector \Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector */
            return include $this->getPath($__file);
        }

        /** @noRector \Rector\Naming\Rector\Variable\UnderscoreToCamelCaseVariableNameRector */
        return include $__file;
    }

    /**
     * Loads the properties of package.yml.
     */
    public function loadProperties()
    {
        $file = $this->getPath(self::FILE_PACKAGE);
        if (!is_file($file)) {
            $this->propertiesLoaded = true;
            return;
        }

        static $cache = null;
        if (null === $cache) {
            $cache = rex_file::getCache(rex_path::coreCache('packages.cache'));
        }
        $id = $this->getPackageId();

        $isCached = isset($cache[$id]);
        $isBackendAdmin = rex::isBackend() && rex::getUser() && rex::getUser()->isAdmin();
        if (!$isCached || (rex::getConsole() || $isBackendAdmin) && $cache[$id]['timestamp'] < filemtime($file)) {
            try {
                $properties = rex_file::getConfig($file);

                $cache[$id]['timestamp'] = filemtime($file);
                $cache[$id]['data'] = $properties;

                static $registeredShutdown = false;
                if (!$registeredShutdown) {
                    $registeredShutdown = true;
                    register_shutdown_function(static function () use (&$cache) {
                        foreach ($cache as $package => $_) {
                            if (!self::exists($package)) {
                                unset($cache[$package]);
                            }
                        }
                        rex_file::putCache(rex_path::coreCache('packages.cache'), $cache);
                    });
                }
            } catch (rex_yaml_parse_exception $exception) {
                if ($this->isInstalled()) {
                    throw $exception;
                }

                $properties = [];
            }
        } else {
            $properties = $cache[$id]['data'];
        }

        $this->properties = array_intersect_key($this->properties, ['install' => null, 'status' => null]);
        if ($properties) {
            foreach ($properties as $key => $value) {
                if (isset($this->properties[$key])) {
                    continue;
                }
                if ('supportpage' !== $key) {
                    $value = rex_i18n::translateArray($value, false, [$this, 'i18n']);
                } elseif (!preg_match('@^https?://@i', $value)) {
                    $value = 'https://'.$value;
                }
                $this->properties[$key] = $value;
            }
        }
        $this->propertiesLoaded = true;
    }

    /**
     *  Clears the cache of the package.
     *
     * @throws rex_functional_exception
     */
    public function clearCache()
    {
        $cacheDir = $this->getCachePath();
        if (!is_dir($cacheDir)) {
            return;
        }
        if (rex_dir::delete($cacheDir)) {
            return;
        }
        throw new rex_functional_exception($this->i18n('cache_not_writable', $cacheDir));
    }

    public function enlist()
    {
        $folder = $this->getPath();

        // add addon path for i18n
        if (is_readable($folder . 'lang')) {
            rex_i18n::addDirectory($folder . 'lang');
        }
        // add package path for fragment loading
        if (is_readable($folder . 'fragments')) {
            rex_fragment::addDirectory($folder . 'fragments' . DIRECTORY_SEPARATOR);
        }
        // add addon path for class-loading
        if (is_readable($folder . 'lib')) {
            rex_autoload::addDirectory($folder . 'lib');
        }
        if (is_readable($folder . 'vendor')) {
            rex_autoload::addDirectory($folder . 'vendor');
        }
        $autoload = $this->getProperty('autoload');
        if (!is_array($autoload)) {
            return;
        }
        if (!isset($autoload['classes'])) {
            return;
        }
        if (!is_array($autoload['classes'])) {
            return;
        }
        foreach ($autoload['classes'] as $dir) {
            $dir = $this->getPath($dir);
            if (is_readable($dir)) {
                rex_autoload::addDirectory($dir);
            }
        }
    }

    public function boot()
    {
        if (is_readable($this->getPath(self::FILE_BOOT))) {
            $this->includeFile(self::FILE_BOOT);
        }
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
