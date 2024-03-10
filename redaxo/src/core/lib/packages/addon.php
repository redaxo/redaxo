<?php

use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Translation\I18n;

class rex_addon implements rex_addon_interface
{
    public const FILE_PACKAGE = 'package.yml';
    public const FILE_BOOT = 'boot.php';
    public const FILE_INSTALL = 'install.php';
    public const FILE_INSTALL_SQL = 'install.sql';
    public const FILE_UNINSTALL = 'uninstall.php';
    public const FILE_UNINSTALL_SQL = 'uninstall.sql';
    public const FILE_UPDATE = 'update.php';

    private const PROPERTIES_CACHE_FILE = 'packages.cache';

    /**
     * Array of all addons.
     *
     * @var array<non-empty-string, self>
     */
    private static $addons = [];

    /**
     * Name of the package.
     *
     * @var non-empty-string
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
     * @param non-empty-string $name Name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the addon by the given name.
     *
     * @param string $addon Addon name
     *
     * @throws InvalidArgumentException
     *
     * @return rex_addon_interface If the package exists, a `rex_addon` is returned, otherwise a `rex_null_addon`
     */
    public static function get($addon)
    {
        if (!is_string($addon)) {
            throw new InvalidArgumentException('Expecting $addon to be string, but ' . gettype($addon) . ' given!');
        }

        if (!isset(self::$addons[$addon])) {
            return rex_null_addon::getInstance();
        }

        return self::$addons[$addon];
    }

    /**
     * Returns the addon by the given name.
     *
     * @throws RuntimeException if the package does not exist
     */
    public static function require(string $addon): self
    {
        if (!isset(self::$addons[$addon])) {
            throw new RuntimeException(sprintf('Required addon "%s" does not exist.', $addon));
        }

        return self::$addons[$addon];
    }

    /**
     * Returns if the addon exists.
     *
     * @param string $addon Addon name
     *
     * @return bool
     *
     * @psalm-assert-if-true =non-empty-string $addon
     */
    public static function exists($addon)
    {
        return is_string($addon) && isset(self::$addons[$addon]);
    }

    /**
     * @return non-empty-string
     */
    public function getPackageId()
    {
        return $this->getName();
    }

    /**
     * @return non-empty-string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getAddon()
    {
        return $this;
    }

    public function getType()
    {
        return 'addon';
    }

    public function getPath($file = '')
    {
        return Path::addon($this->getName(), $file);
    }

    public function getAssetsPath($file = '')
    {
        return Path::addonAssets($this->getName(), $file);
    }

    public function getAssetsUrl($file = '')
    {
        return rex_url::addonAssets($this->getName(), $file);
    }

    public function getDataPath($file = '')
    {
        return Path::addonData($this->getName(), $file);
    }

    public function getCachePath($file = '')
    {
        return Path::addonCache($this->getName(), $file);
    }

    public function isSystemPackage()
    {
        return in_array($this->getPackageId(), Core::getProperty('system_addons'));
    }

    public function setConfig($key, $value = null)
    {
        return rex_config::set($this->getPackageId(), $key, $value);
    }

    public function getConfig($key = null, $default = null)
    {
        return rex_config::get($this->getPackageId(), $key, $default);
    }

    public function hasConfig($key = null)
    {
        return rex_config::has($this->getPackageId(), $key);
    }

    public function removeConfig($key)
    {
        return rex_config::remove($this->getPackageId(), $key);
    }

    public function setProperty($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        $this->properties[$key] = $value;
    }

    public function getProperty($key, $default = null)
    {
        if ($this->hasProperty($key)) {
            return $this->properties[$key];
        }
        return $default;
    }

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

    public function removeProperty($key)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('Expecting $key to be string, but ' . gettype($key) . ' given!');
        }
        unset($this->properties[$key]);
    }

    public function isAvailable()
    {
        return $this->isInstalled() && (bool) $this->getProperty('status', false);
    }

    public function isInstalled()
    {
        return (bool) $this->getProperty('install', false);
    }

    public function getAuthor($default = null)
    {
        $author = (string) $this->getProperty('author', '');

        return '' === $author ? $default : $author;
    }

    public function getVersion($format = null)
    {
        $version = (string) $this->getProperty('version');

        if ($format) {
            return rex_formatter::version($version, $format);
        }
        return $version;
    }

    public function getSupportPage($default = null)
    {
        $supportPage = (string) $this->getProperty('supportpage', '');

        return '' === $supportPage ? $default : $supportPage;
    }

    public function includeFile($file, array $context = [])
    {
        $__file = $file;
        $__context = $context;

        unset($file, $context);

        extract($__context, EXTR_SKIP);

        if (is_file($__path = $this->getPath($__file))) {
            return require $__path;
        }

        if (is_file($__file)) {
            return require $__file;
        }

        throw new rex_exception(sprintf('Package "%s": the page path "%s" neither exists as standalone path nor as package subpath "%s"', $this->getPackageId(), $__file, $__path));
    }

    public function i18n($key, ...$replacements)
    {
        $args = func_get_args();
        $key = $this->getName() . '_' . $key;
        if (I18n::hasMsgOrFallback($key)) {
            $args[0] = $key;
        }
        return call_user_func_array(I18n::msg(...), $args);
    }

    /**
     * Loads the properties of package.yml.
     * @return void
     */
    public function loadProperties(bool $force = false)
    {
        $file = $this->getPath(self::FILE_PACKAGE);
        if (!is_file($file)) {
            $this->propertiesLoaded = true;
            return;
        }

        /** @var array<string, array{timestamp: int, data: array<string, mixed>}>|null $cache */
        static $cache = null;
        if (null === $cache) {
            /** @var array<string, array{timestamp: int, data: array<string, mixed>}> $cache */
            $cache = File::getCache(Path::coreCache(self::PROPERTIES_CACHE_FILE));
        }
        $id = $this->getPackageId();

        if ($force) {
            unset($cache[$id]);
        }

        $isCached = isset($cache[$id]);
        $isBackendAdmin = Core::isBackend() && Core::getUser()?->isAdmin();
        if (!$isCached || (Core::getConsole() || $isBackendAdmin) && $cache[$id]['timestamp'] < filemtime($file)) {
            try {
                $properties = File::getConfig($file);

                $cache[$id]['timestamp'] = filemtime($file);
                $cache[$id]['data'] = $properties;

                /** @var bool $registeredShutdown */
                static $registeredShutdown = false;
                if (!$registeredShutdown) {
                    $registeredShutdown = true;
                    register_shutdown_function(static function () use (&$cache) {
                        foreach ($cache as $package => $_) {
                            if (!self::exists($package)) {
                                unset($cache[$package]);
                            }
                        }
                        File::putCache(Path::coreCache(self::PROPERTIES_CACHE_FILE), $cache);
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
                    $value = I18n::translateArray($value, false, $this->i18n(...));
                } elseif (null !== $value && !preg_match('@^https?://@i', $value)) {
                    $value = 'https://' . $value;
                }
                $this->properties[$key] = $value;
            }
        }
        $this->propertiesLoaded = true;
    }

    /**
     * Clears the cache of the package.
     *
     * @throws rex_functional_exception
     * @return void
     */
    public function clearCache()
    {
        $cacheDir = $this->getCachePath();
        if (!Dir::delete($cacheDir)) {
            throw new rex_functional_exception($this->i18n('cache_not_writable', $cacheDir));
        }

        $cache = File::getCache($path = Path::coreCache(self::PROPERTIES_CACHE_FILE));
        if ($cache) {
            unset($cache[$this->getPackageId()]);
            File::putCache($path, $cache);
        }

        rex_extension::registerPoint(new rex_extension_point_package_cache_deleted($this));
    }

    /**
     * @return void
     */
    public function enlist()
    {
        $folder = $this->getPath();

        // add addon path for i18n
        if (is_readable($folder . 'lang')) {
            I18n::addDirectory($folder . 'lang');
        }
        // add package path for fragment loading
        if (is_readable($folder . 'fragments')) {
            rex_fragment::addDirectory($folder . 'fragments' . DIRECTORY_SEPARATOR);
        }
    }

    /**
     * @return void
     */
    public function boot()
    {
        if (is_readable($this->getPath(self::FILE_BOOT))) {
            $this->includeFile(self::FILE_BOOT);
        }
    }

    /**
     * Returns the registered addons.
     *
     * @return array<non-empty-string, self>
     */
    public static function getRegisteredAddons()
    {
        return self::$addons;
    }

    /**
     * Returns the installed addons.
     *
     * @return array<non-empty-string, self>
     */
    public static function getInstalledAddons()
    {
        return self::filterPackages(self::$addons, 'isInstalled');
    }

    /**
     * Returns the available addons.
     *
     * @return array<non-empty-string, self>
     */
    public static function getAvailableAddons()
    {
        return self::filterPackages(self::$addons, 'isAvailable');
    }

    /**
     * Returns the setup addons.
     *
     * @return array<string, self>
     */
    public static function getSetupAddons()
    {
        $addons = [];
        foreach ((array) Core::getProperty('setup_addons', []) as $addon) {
            if (self::exists($addon)) {
                $addons[$addon] = self::require($addon);
            }
        }
        return $addons;
    }

    /**
     * Returns the system addons.
     *
     * @return array<string, self>
     */
    public static function getSystemAddons()
    {
        $addons = [];
        foreach ((array) Core::getProperty('system_addons', []) as $addon) {
            if (self::exists($addon)) {
                $addons[$addon] = self::require($addon);
            }
        }
        return $addons;
    }

    /**
     * Initializes all packages.
     * @param bool $dbExists
     * @return void
     */
    public static function initialize($dbExists = true)
    {
        if ($dbExists) {
            $config = Core::getPackageConfig();
        } else {
            $config = [];
            foreach (Core::getProperty('setup_addons') as $addon) {
                $config[(string) $addon]['install'] = false;
            }
        }
        $addons = self::$addons;
        self::$addons = [];
        foreach ($config as $addonName => $addonConfig) {
            $addon = $addons[$addonName] ?? new self($addonName);
            $addon->setProperty('install', $addonConfig['install'] ?? false);
            $addon->setProperty('status', $addonConfig['status'] ?? false);
            self::$addons[$addonName] = $addon;
        }
    }

    /**
     * Filters packages by the given method.
     *
     * @param array<non-empty-string, self> $packages Array of packages
     * @param string $method A rex_addon method
     * @return array<non-empty-string, self>
     */
    private static function filterPackages(array $packages, $method)
    {
        return array_filter($packages, static function (rex_addon $package) use ($method): bool {
            $return = $package->$method();
            assert(is_bool($return));

            return $return;
        });
    }
}
