<?php

/**
 * @package redaxo\media-manager
 */
class rex_media_manager
{
    private $media;
    private $originalFilename;
    private $cache_path;
    private $type;
    private $use_cache;
    private $cache;

    private static $effects = [];

    public function __construct(rex_managed_media $media)
    {
        $this->media = $media;
        $this->originalFilename = $media->getMediaFilename();
        $this->useCache(true);
    }

    /**
     * Creates a rex_managed_media object for the given file and mediatype.
     * This object might be used to determine the dimension of a image or similar.
     *
     * @param string $type Media type
     * @param string $file Media file
     *
     * @return self
     */
    public static function create($type, $file)
    {
        $mediaPath = rex_path::media($file);
        $cachePath = rex_path::addonCache('media_manager');

        $media = new rex_managed_media($mediaPath);
        $manager = new self($media);
        $manager->setCachePath($cachePath);
        $manager->applyEffects($type);

        if ($manager->use_cache && $manager->isCached()) {
            $media->setSourcePath($manager->getCacheFilename());

            $cache = $manager->getHeaderCache();

            $media->setFormat($cache['format']);

            foreach ($cache['headers'] as $key => $value) {
                $media->setHeader($key, $value);
            }
        } elseif ($manager->use_cache) {
            $media->save($manager->getCacheFilename(), $manager->getHeaderCacheFilename());
        }

        $media->refreshImageDimensions();

        return $manager;
    }

    /**
     * @return rex_managed_media
     */
    public function getMedia()
    {
        return $this->media;
    }

    protected function applyEffects($type)
    {
        $this->type = $type;

        if (!$this->isCached()) {
            $set = $this->effectsFromType($type);
            $set = rex_extension::registerPoint(new rex_extension_point('MEDIA_MANAGER_FILTERSET', $set, ['rex_media_type' => $type]));

            if (count($set) == 0) {
                $this->use_cache = false;
                return $this->media;
            }

            // execute effects on image
            foreach ($set as $effect_params) {
                $effect_class = 'rex_effect_' . $effect_params['effect'];
                /** @var rex_effect_abstract $effect */
                $effect = new $effect_class();
                $effect->setMedia($this->media);
                $effect->setParams($effect_params['params']);
                $effect->execute();
            }
        }
    }

    public function effectsFromType($type)
    {
        $qry = '
            SELECT e.*
            FROM ' . rex::getTablePrefix() . 'media_manager_type t, ' . rex::getTablePrefix() . 'media_manager_type_effect e
            WHERE e.type_id = t.id AND t.name=? order by e.priority';

        $sql = rex_sql::factory();
        // $sql->setDebug();
        $sql->setQuery($qry, [$type]);

        $effects = [];
        foreach ($sql as $row) {
            $effname = $row->getValue('effect');
            $params = json_decode($row->getValue('parameters'), true);
            $effparams = [];

            // extract parameter out of array
            if (isset($params['rex_effect_' . $effname])) {
                foreach ($params['rex_effect_' . $effname] as $name => $value) {
                    $effparams[str_replace('rex_effect_' . $effname . '_', '', $name)] = $value;
                    unset($effparams[$name]);
                }
            }

            $effect = [
                'effect' => $effname,
                'params' => $effparams,
            ];

            $effects[] = $effect;
        }

        return $effects;
    }

    public function setCachePath($cache_path = '')
    {
        $this->cache_path = $cache_path;
    }

    public function getCachePath()
    {
        return $this->cache_path;
    }

    protected function useCache($t = true)
    {
        $this->use_cache = $t;
    }

    public function isCached()
    {
        $cache_file = $this->getCacheFilename();

        if (!file_exists($cache_file)) {
            return false;
        }

        $cache = $this->getHeaderCache();

        if (!$cache) {
            return false;
        }

        $mediapath = $cache['media_path'];

        if (null === $mediapath) {
            return true;
        }

        if (!file_exists($mediapath)) {
            return false;
        }

        $cachetime = filemtime($cache_file);
        $filetime = filemtime($mediapath);

        // cache is newer?
        return $cachetime > $filetime;
    }

    public function getCacheFilename()
    {
        return $this->cache_path.$this->type.'/'.$this->originalFilename;
    }

    public function getHeaderCacheFilename()
    {
        return $this->getCacheFilename() . '.header';
    }

    private function getHeaderCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        return $this->cache = rex_file::getCache($this->getHeaderCacheFilename(), null);
    }

    public static function deleteCacheByType($type_id)
    {
        $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'media_manager_type' . ' WHERE id=?';
        $sql = rex_sql::factory();
        //  $sql->setDebug();
        $sql->setQuery($qry, [$type_id]);
        $counter = 0;
        foreach ($sql as $row) {
            $counter += self::deleteCache(null, $row->getValue('name'));
        }
        return $counter;
    }

    public static function deleteCache($filename = null, $type = null)
    {
        $filename = ($filename ?: '').'*';

        if (!$type) {
            $type = '*';
        }

        $counter = 0;
        $folder = rex_path::addonCache('media_manager');

        $glob = glob($folder.$type.'/'.$filename, GLOB_NOSORT);
        if ($glob) {
            foreach ($glob as $file) {
                if (rex_file::delete($file)) {
                    ++$counter;
                }
            }
        }

        return $counter;
    }

    public function sendMedia()
    {
        $headerCacheFilename = $this->getHeaderCacheFilename();
        $CacheFilename = $this->getCacheFilename();

        rex_response::cleanOutputBuffers();

        // prevent session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }

        if ($this->use_cache && $this->isCached()) {
            $header = $this->getHeaderCache()['headers'];
            if (isset($header['Last-Modified'])) {
                rex_response::sendLastModified(strtotime($header['Last-Modified']));
                unset($header['Last-Modified']);
            }
            foreach ($header as $t => $c) {
                header($t . ': ' . $c);
            }
            readfile($CacheFilename);
        } else {
            $this->media->sendMedia($CacheFilename, $headerCacheFilename, $this->use_cache);
        }
        exit;
    }

    public static function getSupportedEffects()
    {
        $dirs = [
            __DIR__ . '/effects/',
        ];

        $effects = [];
        foreach ($dirs as $dir) {
            $files = glob($dir . 'effect_*.php');
            if ($files) {
                foreach ($files as $file) {
                    $effects[self::getEffectClass($file)] = self::getEffectName($file);
                }
            }
        }

        foreach (self::$effects as $class) {
            $effects[$class] = str_replace(['rex_', 'effect_'], '', $class);
        }

        return $effects;
    }

    public static function addEffect($class)
    {
        self::$effects[] = $class;
    }

    private static function getEffectName($effectFile)
    {
        return str_replace(
            ['effect_', '.php'],
            '',
            basename($effectFile)
        );
    }

    private static function getEffectClass($effectFile)
    {
        return 'rex_' . str_replace(
            '.php',
            '',
            basename($effectFile)
        );
    }

    /*
     * For ExtensionPoints.
     */
    public static function mediaUpdated(rex_extension_point $ep)
    {
        self::deleteCache($ep->getParam('filename'));
    }

    public static function init()
    {
        //--- handle image request
        $rex_media_manager_file = self::getMediaFile();
        $rex_media_manager_type = self::getMediaType();

        if ($rex_media_manager_file != '' && $rex_media_manager_type != '') {
            $media_path = rex_path::media($rex_media_manager_file);
            $cache_path = rex_path::addonCache('media_manager');

            $media = new rex_managed_media($media_path);
            $media_manager = new self($media);
            $media_manager->setCachePath($cache_path);
            $media_manager->applyEffects($rex_media_manager_type);
            $media_manager->sendMedia();

            exit();
        }
    }

    public static function getMediaFile()
    {
        $rex_media_file = rex_get('rex_media_file', 'string');

        // can be used with REDAXO >= 5.5.1
        // $rex_media_file = rex_path::basename($rex_media_file);
        $rex_media_file = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $rex_media_file);
        $rex_media_file = basename($rex_media_file);

        return $rex_media_file;
    }

    public static function getMediaType()
    {
        $type = rex_get('rex_media_type', 'string');

        // can be used with REDAXO >= 5.5.1
        // $type = rex_path::basename($type);
        $type = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $type);
        $type = basename($type);

        return $type;
    }
}
