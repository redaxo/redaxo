<?php

namespace Redaxo\Core\Addon;

use Override;
use Redaxo\Core\Core;
use Redaxo\Core\Filesystem\Dir;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\Filesystem\Path;
use Redaxo\Core\Filesystem\Url;
use Redaxo\Core\Translation\I18n;
use Redaxo\Core\Util\Formatter;
use Redaxo\Core\Util\Type;
use rex_config;
use rex_exception;
use rex_extension;
use rex_extension_point_package_cache_deleted;
use rex_fragment;
use rex_functional_exception;
use rex_yaml_parse_exception;
use RuntimeException;

use function assert;
use function in_array;
use function is_bool;

use const DIRECTORY_SEPARATOR;
use const EXTR_SKIP;

final class Addon implements AddonInterface
{
    public const string FILE_PACKAGE = 'package.yml';
    public const string FILE_BOOT = 'boot.php';
    public const string FILE_INSTALL = 'install.php';
    public const string FILE_INSTALL_SQL = 'install.sql';
    public const string FILE_UNINSTALL = 'uninstall.php';
    public const string FILE_UNINSTALL_SQL = 'uninstall.sql';
    public const string FILE_UPDATE = 'update.php';

    private const string PROPERTIES_CACHE_FILE = 'packages.cache';

    /**
     * Array of all addons.
     *
     * @var array<non-empty-string, self>
     */
    private static array $addons = [];

    /**
     * Properties.
     *
     * @var array<string, mixed>
     */
    private array $properties = [];

    /**
     * Flag whether the properties of package.yml are loaded.
     */
    private bool $propertiesLoaded = false;

    /**
     * @param non-empty-string $name Name of the addon
     */
    public function __construct(
        private readonly string $name,
    ) {}

    /**
     * Returns the addon by the given name.
     *
     * @param string $addon Addon name
     * @return AddonInterface If the addon exists, a `Addon` is returned, otherwise a `NullAddon`
     */
    public static function get(string $addon): AddonInterface
    {
        if (!isset(self::$addons[$addon])) {
            return NullAddon::getInstance();
        }

        return self::$addons[$addon];
    }

    /**
     * Returns the addon by the given name.
     *
     * @throws RuntimeException if the addon does not exist
     * @psalm-assert =non-empty-string $addon
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
     * @psalm-assert-if-true =non-empty-string $addon
     */
    public static function exists(string $addon): bool
    {
        return isset(self::$addons[$addon]);
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getPackageId(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getPath(string $file = ''): string
    {
        return Path::addon($this->name, $file);
    }

    #[Override]
    public function getAssetsPath(string $file = ''): string
    {
        return Path::addonAssets($this->name, $file);
    }

    #[Override]
    public function getAssetsUrl(string $file = ''): string
    {
        return Url::addonAssets($this->name, $file);
    }

    #[Override]
    public function getDataPath(string $file = ''): string
    {
        return Path::addonData($this->name, $file);
    }

    #[Override]
    public function getCachePath(string $file = ''): string
    {
        return Path::addonCache($this->name, $file);
    }

    #[Override]
    public function isSystemPackage(): bool
    {
        return in_array($this->name, Core::getProperty('system_addons'));
    }

    #[Override]
    public function setConfig(string|array $key, mixed $value = null): bool
    {
        return rex_config::set($this->name, $key, $value);
    }

    #[Override]
    public function getConfig(?string $key = null, mixed $default = null): mixed
    {
        return rex_config::get($this->name, $key, $default);
    }

    #[Override]
    public function hasConfig(?string $key = null): bool
    {
        return rex_config::has($this->name, $key);
    }

    #[Override]
    public function removeConfig(string $key): bool
    {
        return rex_config::remove($this->name, $key);
    }

    #[Override]
    public function setProperty(string $key, mixed $value): void
    {
        $this->properties[$key] = $value;
    }

    #[Override]
    public function getProperty(string $key, mixed $default = null): mixed
    {
        if ($this->hasProperty($key)) {
            return $this->properties[$key];
        }
        return $default;
    }

    #[Override]
    public function hasProperty(string $key): bool
    {
        if (!isset($this->properties[$key]) && !$this->propertiesLoaded) {
            $this->loadProperties();
        }
        return isset($this->properties[$key]);
    }

    #[Override]
    public function removeProperty(string $key): void
    {
        unset($this->properties[$key]);
    }

    #[Override]
    public function isAvailable(): bool
    {
        return $this->isInstalled() && (bool) $this->getProperty('status', false);
    }

    #[Override]
    public function isInstalled(): bool
    {
        return (bool) $this->getProperty('install', false);
    }

    #[Override]
    public function getAuthor(?string $default = null): ?string
    {
        $author = (string) $this->getProperty('author', '');

        return '' === $author ? $default : $author;
    }

    #[Override]
    public function getVersion(?string $format = null): string
    {
        $version = (string) $this->getProperty('version');

        if ($format) {
            return Formatter::version($version, $format);
        }
        return $version;
    }

    #[Override]
    public function getSupportPage(?string $default = null): ?string
    {
        $supportPage = (string) $this->getProperty('supportpage', '');

        return '' === $supportPage ? $default : $supportPage;
    }

    #[Override]
    public function includeFile(string $file, array $context = []): mixed
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

        throw new rex_exception(sprintf('Addon "%s": the page path "%s" neither exists as standalone path nor as addon subpath "%s"', $this->name, $__file, $__path));
    }

    #[Override]
    public function i18n(string $key, string|int ...$replacements): string
    {
        $fullKey = $this->name . '_' . $key;
        if (I18n::hasMsgOrFallback($fullKey)) {
            $key = $fullKey;
        }
        return I18n::msg($key, ...$replacements);
    }

    /**
     * Loads the properties of package.yml.
     */
    public function loadProperties(bool $force = false): void
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
        $id = $this->name;

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
                        foreach ($cache as $addon => $_) {
                            if (!self::exists($addon)) {
                                unset($cache[$addon]);
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
                Type::string($key);
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

    public function getLicense(): ?string
    {
        $license = trim((string) $this->getProperty('license', ''));

        if ('' !== $license) {
            return $license;
        }

        if (is_readable($licenseFile = $this->getPath('LICENSE')) || is_readable($licenseFile = $this->getPath('LICENSE.md'))) {
            $f = fopen($licenseFile, 'r');
            $license = trim(fgets($f) ?: '');
            fclose($f);

            if (preg_match('/^The MIT License(?: \(MIT\))$/i', $license)) {
                return 'MIT License';
            }
        }

        return $license ?: null;
    }

    /**
     * Clears the cache of the addon.
     *
     * @throws rex_functional_exception
     */
    public function clearCache(): void
    {
        $cacheDir = $this->getCachePath();
        if (!Dir::delete($cacheDir)) {
            throw new rex_functional_exception($this->i18n('cache_not_writable', $cacheDir));
        }

        $cache = File::getCache($path = Path::coreCache(self::PROPERTIES_CACHE_FILE));
        if ($cache) {
            unset($cache[$this->name]);
            File::putCache($path, $cache);
        }

        rex_extension::registerPoint(new rex_extension_point_package_cache_deleted($this));
    }

    public function enlist(): void
    {
        $folder = $this->getPath();

        // add addon path for i18n
        if (is_readable($folder . 'lang')) {
            I18n::addDirectory($folder . 'lang');
        }
        // add addon path for fragment loading
        if (is_readable($folder . 'fragments')) {
            rex_fragment::addDirectory($folder . 'fragments' . DIRECTORY_SEPARATOR);
        }
    }

    public function boot(): void
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
    public static function getRegisteredAddons(): array
    {
        return self::$addons;
    }

    /**
     * Returns the installed addons.
     *
     * @return array<non-empty-string, self>
     */
    public static function getInstalledAddons(): array
    {
        return self::filterPackages(self::$addons, 'isInstalled');
    }

    /**
     * Returns the available addons.
     *
     * @return array<non-empty-string, self>
     */
    public static function getAvailableAddons(): array
    {
        return self::filterPackages(self::$addons, 'isAvailable');
    }

    /**
     * Returns the setup addons.
     *
     * @return array<non-empty-string, self>
     */
    public static function getSetupAddons(): array
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
     * @return array<non-empty-string, self>
     */
    public static function getSystemAddons(): array
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
     * Initializes all addons.
     */
    public static function initialize(bool $dbExists = true): void
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
     * Filters addons by the given method.
     *
     * @param array<non-empty-string, self> $addons Array of addons
     * @param string $method A Addon method
     * @return array<non-empty-string, Addon>
     */
    private static function filterPackages(array $addons, string $method): array
    {
        return array_filter($addons, static function (Addon $addon) use ($method): bool {
            $return = $addon->$method();
            assert(is_bool($return));

            return $return;
        });
    }
}
