<?php

/**
 * @package redaxo\media-manager
 */
class rex_media_manager
{
    private $cache_path;
    private $type;
    private $use_cache;

    private static $effects = [];

    public function __construct(rex_managed_media $media)
    {
        $this->media = $media;
        $this->useCache(true);
    }

    /**
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

        if ($manager->isCached()) {
            $media->setSourcePath($manager->getCacheFilename());

            $cache = rex_file::getCache($manager->getHeaderCacheFilename());

            $media->setFormat($cache['format']);

            foreach ($cache['headers'] as $key => $value) {
                $media->setHeader($key, $value);
            }
        } else {
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

        if (!$this->isCached($type)) {
            $set = $this->effectsFromType($type);
            $set = rex_extension::registerPoint(new rex_extension_point('MEDIA_MANAGER_FILTERSET', $set, ['rex_media_type' => $type]));

            if (count($set) == 0) {
                return $this->media;
            }

            // execute effects on image
            foreach ($set as $effect_params) {
                $effect_class = 'rex_effect_' . $effect_params['effect'];
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
            WHERE e.type_id = t.id AND t.name="' . $type . '" order by e.priority';

        $sql = rex_sql::factory();
        // $sql->setDebug();
        $sql->setQuery($qry);

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

        // ----- check for cache file
        if (file_exists($cache_file)) {
            // time of cache
            $cachetime = filectime($cache_file);
            $mediapath = $this->media->getMediaPath();

            $filetime = filectime($mediapath);
            // cache is newer?
            if ($cachetime > $filetime) {
                return true;
            }
        }

        return false;
    }

    public function getCacheFilename()
    {
        $cacheParams = $this->type . '_' . md5(serialize($this->media->getMediapath()));
        return $this->cache_path . $this->media->getMediaFilename() . '_' . $cacheParams;
    }

    public function getHeaderCacheFilename()
    {
        return $this->getCacheFilename() . '.header';
    }

    public static function deleteCacheByType($type_id)
    {
        $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'media_manager_type' . ' WHERE id=' . $type_id;
        $sql = rex_sql::factory();
        //  $sql->setDebug();
        $sql->setQuery($qry);
        $counter = 0;
        foreach ($sql as $row) {
            $counter += self::deleteCache(null, $row->getValue('name'));
        }
        return $counter;
    }

    public static function deleteCache($filename = null, $cacheParams = null)
    {
        if (!$filename) {
            $filename = '*';
        }

        if (!$cacheParams) {
            $cacheParams = '*';
        }

        $counter = 0;
        $folder = rex_path::addonCache('media_manager');

        $glob = glob($folder . $filename . '_' . $cacheParams . '*');
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

        if ($this->isCached()) {
            $header = rex_file::getCache($headerCacheFilename)['headers'];
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
        $rex_media_file = basename($rex_media_file);
        return $rex_media_file;
    }

    public static function getMediaType()
    {
        return rex_get('rex_media_type', 'string');
    }
    
    public static function getUrl($filename = '', $type = '', $rewrite = true)
    {
        if($rewrite) {
            $rewrite = '/images/' . $type . '/' . $filename;
        } else {
            $rewrite = 'index.php?rex_media_type=' . $type . '&rex_media_file=' . $filename;
        }

        return $rewrite;
    }
    
}
