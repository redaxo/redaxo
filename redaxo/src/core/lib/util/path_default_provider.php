<?php

/**
 * Utility class to generate absolute paths.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_path_default_provider
{
    protected  $base;
    protected  $backend;
    protected  $provideAbsolutes;

    /**
     * Initializes the class.
     *
     * @param string $htdocs  Htdocs path
     * @param string $backend Backend folder name
     * @param boolean $provideAbsolutes Flag whether to return absolute path, or relative ones
     */
    public function __construct($htdocs, $backend, $provideAbsolutes)
    {
        if ($provideAbsolutes) {
            $this->base = realpath($htdocs) . '/';
            $this->backend = $backend;
        } else {
            $this->base = $htdocs;
            $this->backend = substr($htdocs, -3) === '../' ? '' : $htdocs . $backend . '/';
        }
        $this->provideAbsolutes = $provideAbsolutes;
    }

    /**
     * Returns a base path.
     *
     * @param string $file File
     *
     * @return string
     */
    public function base($file)
    {
        if ($this->provideAbsolutes) {
            return strtr($this->base . $file, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        } else {
            return $this->base . $file;
        }
    }

    /**
     * Returns the path to the frontend.
     *
     * @param string $file File
     *
     * @return string
     */
    public function frontend($file)
    {
        return self::base($file);
    }

    /**
     * Returns the path to the frontend-controller (index.php from frontend).
     *
     * @return string
     */
    public function frontendController()
    {
        return self::base('index.php');
    }

    /**
     * Returns the path to the backend.
     *
     * @param string $file File
     *
     * @return string
     */
    public  function backend($file)
    {
        if ($this->provideAbsolutes) {
            return self::base($this->backend . '/' . $file);
        } else {
            return $this->backend . $file;
        }
    }

    /**
     * Returns the path to the backend-controller (index.php from backend).
     *
     * @return string
     */
    public function backendController()
    {
        return self::backend('index.php');
    }

    /**
     * Returns the path to the media-folder.
     *
     * @param string $file File
     *
     * @return string
     */
    public function media($file)
    {
        return self::base('media/' . $file);
    }

    /**
     * Returns the path to the assets folder of the core, which contains all assets required by the core to work properly.
     *
     * @param string $file File
     *
     * @return string
     */
    public function assets($file)
    {
        return self::base('assets/' . $file);
    }

    /**
     * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     *
     * @see assets()
     */
    public function addonAssets($addon, $file)
    {
        return self::assets('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the assets folder of the given plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     *
     * @see assets()
     */
    public function pluginAssets($addon, $plugin, $file)
    {
        return self::addonAssets($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the data folder of the core.
     *
     * @param string $file File
     *
     * @return string
     */
    public function data($file)
    {
        return self::backend('data/' . $file);
    }

    /**
     * Returns the path to the data folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     */
    public function addonData($addon, $file)
    {
        return self::data('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the data folder of the given plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     */
    public function pluginData($addon, $plugin, $file)
    {
        return self::addonData($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the cache folder of the core.
     *
     * @param string $file File
     *
     * @return string
     */
    public function cache($file)
    {
        return self::backend('cache/' . $file);
    }

    /**
     * Returns the path to the cache folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     */
    public function addonCache($addon, $file)
    {
        return self::cache('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the cache folder of the given plugin.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     */
    public function pluginCache($addon, $plugin, $file)
    {
        return self::addonCache($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the src folder.
     *
     * @param string $file File
     *
     * @return string
     */
    public function src($file)
    {
        return self::backend('src/' . $file);
    }

    /**
     * Returns the path to the actual core.
     *
     * @param string $file File
     *
     * @return string
     */
    public  function core($file)
    {
        return self::src('core/' . $file);
    }

    /**
     * Returns the base path to the folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return string
     */
    public function addon($addon, $file)
    {
        return self::src('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the base path to the folder of the plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return string
     */
    public function plugin($addon, $plugin, $file)
    {
        return self::addon($addon, 'plugins/' . $plugin . '/' . $file);
    }

}
