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
    /** @var non-empty-string */
    protected $base;
    /** @var string */
    protected $backend;
    /** @var bool */
    protected $provideAbsolutes;

    /**
     * Initializes the class.
     *
     * @param non-empty-string $htdocs           Htdocs path
     * @param non-empty-string $backend          Backend folder name
     * @param bool   $provideAbsolutes Flag whether to return absolute path, or relative ones
     */
    public function __construct($htdocs, $backend, $provideAbsolutes)
    {
        if ($provideAbsolutes) {
            $this->base = realpath($htdocs) . '/';
            $this->backend = $backend;
        } else {
            $this->base = $htdocs;
            $this->backend = str_ends_with($htdocs, '../') ? '' : $htdocs . $backend . '/';
        }
        $this->provideAbsolutes = $provideAbsolutes;
    }

    /**
     * Returns the base/root path.
     *
     * @param string $file File
     *
     * @return non-empty-string
     *
     * @psalm-taint-specialize
     */
    public function base($file)
    {
        if ($this->provideAbsolutes) {
            return strtr($this->base . $file, '/\\', DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR);
        }
        return $this->base . $file;
    }

    /**
     * Returns the path to the frontend (the document root).
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function frontend($file)
    {
        return $this->base($file);
    }

    /**
     * Returns the path to the frontend-controller (index.php from frontend).
     *
     * @return non-empty-string
     */
    public function frontendController()
    {
        return $this->base('index.php');
    }

    /**
     * Returns the path to the backend (folder where the backend controller is placed).
     *
     * @param string $file File
     *
     * @return non-empty-string
     *
     * @psalm-taint-specialize
     */
    public function backend($file = '')
    {
        if ($this->provideAbsolutes) {
            return $this->frontend($this->backend . '/' . $file);
        }

        if ('' === $this->backend . $file) {
            throw new InvalidArgumentException('Empty path given.');
        }
        return $this->backend . $file;
    }

    /**
     * Returns the path to the backend-controller (index.php from backend).
     *
     * @return non-empty-string
     */
    public function backendController()
    {
        return $this->backend('index.php');
    }

    /**
     * Returns the path to the media-folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function media($file)
    {
        return $this->frontend('media/' . $file);
    }

    /**
     * Returns the path to the assets folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function assets($file)
    {
        return $this->frontend('assets/' . $file);
    }

    /**
     * Returns the path to the assets folder of the core, which contains all assets required by the core to work properly.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function coreAssets($file)
    {
        return $this->assets('core/' . $file);
    }

    /**
     * Returns the path to the assets folder of the given addon, which contains all assets required by the addon to work properly.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return non-empty-string
     *
     * @see assets()
     */
    public function addonAssets($addon, $file)
    {
        return $this->assets('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the assets folder of the given plugin of the given addon.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return non-empty-string
     *
     * @see assets()
     */
    public function pluginAssets($addon, $plugin, $file)
    {
        return $this->addonAssets($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the bin folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function bin($file)
    {
        return $this->backend('bin/' . $file);
    }

    /**
     * Returns the path to the data folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function data($file)
    {
        return $this->backend('data/' . $file);
    }

    /**
     * Returns the path to the data folder of the core.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function coreData($file)
    {
        return $this->data('core/' . $file);
    }

    /**
     * Returns the path to the data folder of the given addon.
     *
     * @param non-empty-string $addon Addon
     * @param string $file  File
     *
     * @return non-empty-string
     */
    public function addonData($addon, $file)
    {
        return $this->data('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the data folder of the given plugin of the given addon.
     *
     * @param non-empty-string $addon  Addon
     * @param non-empty-string $plugin Plugin
     * @param string $file   File
     *
     * @return non-empty-string
     */
    public function pluginData($addon, $plugin, $file)
    {
        return $this->addonData($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the log folder.
     *
     * @return non-empty-string
     */
    public function log(string $file): string
    {
        return $this->data('log/'.$file);
    }

    /**
     * Returns the path to the cache folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function cache($file)
    {
        return $this->backend('cache/' . $file);
    }

    /**
     * Returns the path to the cache folder of the core.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function coreCache($file)
    {
        return $this->cache('core/' . $file);
    }

    /**
     * Returns the path to the cache folder of the given addon.
     *
     * @param string $addon Addon
     * @param string $file  File
     *
     * @return non-empty-string
     */
    public function addonCache($addon, $file)
    {
        return $this->cache('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the path to the cache folder of the given plugin.
     *
     * @param string $addon  Addon
     * @param string $plugin Plugin
     * @param string $file   File
     *
     * @return non-empty-string
     */
    public function pluginCache($addon, $plugin, $file)
    {
        return $this->addonCache($addon, 'plugins/' . $plugin . '/' . $file);
    }

    /**
     * Returns the path to the src folder.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function src($file)
    {
        return $this->backend('src/' . $file);
    }

    /**
     * Returns the path to the actual core.
     *
     * @param string $file File
     *
     * @return non-empty-string
     */
    public function core($file)
    {
        return $this->src('core/' . $file);
    }

    /**
     * Returns the base path to the folder of the given addon.
     *
     * @param non-empty-string $addon Addon
     * @param string $file  File
     *
     * @return non-empty-string
     */
    public function addon($addon, $file)
    {
        return $this->src('addons/' . $addon . '/' . $file);
    }

    /**
     * Returns the base path to the folder of the plugin of the given addon.
     *
     * @param non-empty-string $addon  Addon
     * @param non-empty-string $plugin Plugin
     * @param string $file   File
     *
     * @return non-empty-string
     */
    public function plugin($addon, $plugin, $file)
    {
        return $this->addon($addon, 'plugins/' . $plugin . '/' . $file);
    }
}
