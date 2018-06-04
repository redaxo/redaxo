<?php

/**
 * Class for addons.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_addon extends rex_package implements rex_addon_interface
{
    /**
     * Array of all addons.
     *
     * @var rex_addon[]
     */
    private static $addons = [];

    /**
     * Array of all child plugins.
     *
     * @var rex_plugin[]
     */
    private $plugins = [];

    /**
     * Returns the addon by the given name.
     *
     * @param string $addon Name of the addon
     *
     * @throws InvalidArgumentException
     *
     * @return self
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
     * Returns if the addon exists.
     *
     * @param string $addon Name of the addon
     *
     * @return bool
     */
    public static function exists($addon)
    {
        return is_string($addon) && isset(self::$addons[$addon]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAddon()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackageId()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'addon';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($file = '')
    {
        return rex_path::addon($this->getName(), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsPath($file = '')
    {
        return rex_path::addonAssets($this->getName(), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsUrl($file = '')
    {
        return rex_url::addonAssets($this->getName(), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataPath($file = '')
    {
        return rex_path::addonData($this->getName(), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath($file = '')
    {
        return rex_path::addonCache($this->getName(), $file);
    }

    /**
     * {@inheritdoc}
     */
    public function isSystemPackage()
    {
        return in_array($this->getPackageId(), rex::getProperty('system_addons'));
    }

    /**
     * {@inheritdoc}
     */
    public function i18n($key)
    {
        $args = func_get_args();
        $key = $this->getName() . '_' . $key;
        if (rex_i18n::hasMsgOrFallback($key)) {
            $args[0] = $key;
        }
        return call_user_func_array('rex_i18n::msg', $args);
    }

    /**
     * {@inheritdoc}
     */
    public function getPlugin($plugin)
    {
        if (!is_string($plugin)) {
            throw new InvalidArgumentException('Expecting $plugin to be string, but ' . gettype($plugin) . ' given!');
        }
        if (!isset($this->plugins[$plugin])) {
            return rex_null_plugin::getInstance();
        }
        return $this->plugins[$plugin];
    }

    /**
     * {@inheritdoc}
     */
    public function pluginExists($plugin)
    {
        return is_string($plugin) && isset($this->plugins[$plugin]);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegisteredPlugins()
    {
        return $this->plugins;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstalledPlugins()
    {
        return self::filterPackages($this->plugins, 'isInstalled');
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailablePlugins()
    {
        return self::filterPackages($this->plugins, 'isAvailable');
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemPlugins()
    {
        if (rex::isSetup() || rex::isSafeMode()) {
            // in setup and safemode this method is called before the package .lang files are added to rex_i18n
            // so don't use getProperty(), to avoid loading all properties without translations
            $properties = rex_file::getConfig($this->getPath(parent::FILE_PACKAGE));
            $systemPlugins = isset($properties['system_plugins']) ? (array) $properties['system_plugins'] : [];
        } else {
            $systemPlugins = (array) $this->getProperty('system_plugins', []);
        }
        $plugins = [];
        foreach ($systemPlugins as $plugin) {
            if ($this->pluginExists($plugin)) {
                $plugins[$plugin] = $this->getPlugin($plugin);
            }
        }
        return $plugins;
    }

    /**
     * Returns the registered addons.
     *
     * @return self[]
     */
    public static function getRegisteredAddons()
    {
        return self::$addons;
    }

    /**
     * Returns the installed addons.
     *
     * @return self[]
     */
    public static function getInstalledAddons()
    {
        return self::filterPackages(self::$addons, 'isInstalled');
    }

    /**
     * Returns the available addons.
     *
     * @return self[]
     */
    public static function getAvailableAddons()
    {
        return self::filterPackages(self::$addons, 'isAvailable');
    }

    /**
     * Returns the setup addons.
     *
     * @return self[]
     */
    public static function getSetupAddons()
    {
        $addons = [];
        foreach ((array) rex::getProperty('setup_addons', []) as $addon) {
            if (self::exists($addon)) {
                $addons[$addon] = self::get($addon);
            }
        }
        return $addons;
    }

    /**
     * Returns the system addons.
     *
     * @return self[]
     */
    public static function getSystemAddons()
    {
        $addons = [];
        foreach ((array) rex::getProperty('system_addons', []) as $addon) {
            if (self::exists($addon)) {
                $addons[$addon] = self::get($addon);
            }
        }
        return $addons;
    }

    /**
     * Initializes all packages.
     */
    public static function initialize($dbExists = true)
    {
        if ($dbExists) {
            $config = rex::getConfig('package-config', []);
        } else {
            $config = [];
            foreach (rex::getProperty('setup_addons') as $addon) {
                $config[$addon]['install'] = false;
            }
        }
        $addons = self::$addons;
        self::$addons = [];
        foreach ($config as $addonName => $addonConfig) {
            $addon = isset($addons[$addonName]) ? $addons[$addonName] : new self($addonName);
            $addon->setProperty('install', isset($addonConfig['install']) ? $addonConfig['install'] : false);
            $addon->setProperty('status', isset($addonConfig['status']) ? $addonConfig['status'] : false);
            self::$addons[$addonName] = $addon;
            if (!$dbExists && is_array($plugins = $addon->getProperty('system_plugins'))) {
                foreach ($plugins as $plugin) {
                    $config[$addonName]['plugins'][$plugin]['install'] = false;
                }
            }
            if (isset($config[$addonName]['plugins']) && is_array($config[$addonName]['plugins'])) {
                $plugins = $addon->plugins;
                $addon->plugins = [];
                foreach ($config[$addonName]['plugins'] as $pluginName => $pluginConfig) {
                    $plugin = isset($plugins[$pluginName]) ? $plugins[$pluginName] : new rex_plugin($pluginName, $addon);
                    $plugin->setProperty('install', isset($pluginConfig['install']) ? $pluginConfig['install'] : false);
                    $plugin->setProperty('status', isset($pluginConfig['status']) ? $pluginConfig['status'] : false);
                    $addon->plugins[$pluginName] = $plugin;
                }
            }
        }
    }

    /**
     * Filters packages by the given method.
     *
     * @param array  $packages Array of packages
     * @param string $method   A rex_package method
     *
     * @return rex_package[]
     */
    private static function filterPackages(array $packages, $method)
    {
        return array_filter($packages, function (rex_package $package) use ($method) {
            return $package->$method();
        });
    }
}
