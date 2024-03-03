<?php

class rex_addon extends rex_package
{
    /**
     * Array of all addons.
     *
     * @var array<non-empty-string, self>
     */
    private static $addons = [];

    /**
     * Returns the addon by the given name.
     *
     * @param string $addon Name of the addon
     *
     * @throws InvalidArgumentException
     *
     * @return rex_addon_interface If the addon exists, a `rex_addon` is returned, otherwise a `rex_null_addon`
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
     * @throws RuntimeException if the addon does not exist
     *
     * @return self
     */
    public static function require(string $addon): rex_package
    {
        if (!isset(self::$addons[$addon])) {
            throw new RuntimeException(sprintf('Required addon "%s" does not exist.', $addon));
        }

        return self::$addons[$addon];
    }

    /**
     * Returns if the addon exists.
     *
     * @param string $addon Name of the addon
     *
     * @return bool
     *
     * @psalm-assert-if-true =non-empty-string $addon
     */
    public static function exists($addon)
    {
        return is_string($addon) && isset(self::$addons[$addon]);
    }

    public function getAddon()
    {
        return $this;
    }

    /**
     * @return non-empty-string
     */
    public function getPackageId()
    {
        return $this->getName();
    }

    public function getType()
    {
        return 'addon';
    }

    public function getPath($file = '')
    {
        return rex_path::addon($this->getName(), $file);
    }

    public function getAssetsPath($file = '')
    {
        return rex_path::addonAssets($this->getName(), $file);
    }

    public function getAssetsUrl($file = '')
    {
        return rex_url::addonAssets($this->getName(), $file);
    }

    public function getDataPath($file = '')
    {
        return rex_path::addonData($this->getName(), $file);
    }

    public function getCachePath($file = '')
    {
        return rex_path::addonCache($this->getName(), $file);
    }

    public function isSystemPackage()
    {
        return in_array($this->getPackageId(), rex::getProperty('system_addons'));
    }

    public function i18n($key, ...$replacements)
    {
        $args = func_get_args();
        $key = $this->getName() . '_' . $key;
        if (rex_i18n::hasMsgOrFallback($key)) {
            $args[0] = $key;
        }
        return call_user_func_array(rex_i18n::msg(...), $args);
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
        foreach ((array) rex::getProperty('setup_addons', []) as $addon) {
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
        foreach ((array) rex::getProperty('system_addons', []) as $addon) {
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
            $config = rex::getPackageConfig();
        } else {
            $config = [];
            foreach (rex::getProperty('setup_addons') as $addon) {
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
     * @template T of rex_package
     * @param array<non-empty-string, T> $packages Array of packages
     * @param string $method A rex_package method
     * @return array<non-empty-string, T>
     */
    private static function filterPackages(array $packages, $method)
    {
        return array_filter($packages, static function (rex_package $package) use ($method): bool {
            $return = $package->$method();
            assert(is_bool($return));

            return $return;
        });
    }
}
