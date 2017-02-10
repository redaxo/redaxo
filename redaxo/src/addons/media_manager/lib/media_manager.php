<?php

/**
 * @package redaxo\media-manager
 */
class rex_media_manager
{
    private $cache_path;
    private $type;
    private static $effects = [];

    public function __construct(rex_managed_media $media)
    {
        $this->media = $media;
    }

    public static function init()
    {
        $rex_media_manager_file = self::getMediaFile();
        $rex_media_manager_type = self::getMediaType();

        $media = self::getMedia($rex_media_manager_file, $rex_media_manager_type);

        if ($media) {
            self::sendMedia($media);
        }

        return null;
    }

    public static function getMedia($rex_media_manager_file, $rex_media_manager_type)
    {
        $media_path = rex_path::media($rex_media_manager_file);
        $cache_path = rex_path::addonCache('media_manager');

        if ($rex_media_manager_file == '' || $rex_media_manager_type == '') {
            return null;
        }

        $media = new rex_managed_media($media_path);
        $media_manager = new self($media);
        $media_manager->setCachePath($cache_path);
        $media = $media_manager->applyEffects($rex_media_manager_type);

        $headerCacheFilename = $media_manager->getHeaderCacheFilename();
        $sourceCacheFilename = $media_manager->getSourceCacheFilename();

        if (!$media_manager->isCached()) {
            $src = $media->getSource();
            $media->setHeader('Content-Length', rex_string::size($src));
            $header = $media->getHeader();

            $extended = '';
            if (isset($header['Fileextension'])) {
                $extended = '.'.$header['Fileextension'];
            }

            if (!isset($header['Content-Type'])) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $content_type = finfo_file($finfo, $media->getMediapath());
                if ($content_type != '') {
                    $media->setHeader('Content-Type', $content_type);
                }
            }
            if (!isset($header['Content-Disposition'])) {
                $media->setHeader('Content-Disposition', 'inline; filename="' . $media->getMediaFilename() . $extended . '";');
            }
            if (!isset($header['Last-Modified'])) {
                $media->setHeader('Last-Modified', gmdate('D, d M Y H:i:s T'));
            }

            rex_file::putCache($headerCacheFilename, $media->getHeader());
            rex_file::put($sourceCacheFilename . $extended, $src);
        }

        $header = rex_file::getCache($headerCacheFilename);
        $extended = '';
        if (isset($header['Fileextension'])) {
            $extended = '.'.$header['Fileextension'];
        }
        $media = new rex_managed_media($sourceCacheFilename . $extended);
        $media->setHeaders($header);
        $media->isImage();

        return $media;
    }

    public static function sendMedia($media)
    {
        rex_response::cleanOutputBuffers();

        if (isset($header['Last-Modified'])) {
            rex_response::sendLastModified(strtotime($header['Last-Modified']));
            unset($header['Last-Modified']);
        }
        if (isset($header['Fileextension'])) {
            unset($header['Fileextension']);
        }
        foreach ($media->getHeader() as $t => $c) {
            header($t . ': ' . $c);
        }
        echo $media->getSource();
        exit;
    }

    public function setCachePath($cache_path = '')
    {
        $this->cache_path = $cache_path;
    }

    public function getCachePath()
    {
        return $this->cache_path;
    }

    public function getSourceCacheFilename()
    {
        $cacheParams = $this->type . '_' . md5(serialize($this->media->getMediapath()));
        return $this->cache_path . $this->media->getMediaFilename() . '_' . $cacheParams . '_' . $this->media->getMediaFilename();
    }

    public function getHeaderCacheFilename()
    {
        return $this->getSourceCacheFilename() . '.header';
    }

    public function isCached()
    {
        $cache_file = $this->getHeaderCacheFilename();

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

    public static function deleteCacheByType($type_id)
    {
        $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'media_manager_type' . ' WHERE id=' . $type_id;
        $sql = rex_sql::factory();
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

    protected function applyEffects($type)
    {
        $this->type = $type;

        if (!$this->isCached()) {
            $set = $this->effectsFromType($type);
            $set = rex_extension::registerPoint(new rex_extension_point('MEDIA_MANAGER_FILTERSET', $set, ['rex_media_type' => $type]));

            if (count($set) > 0) {
                foreach ($set as $effect_params) {
                    $effect_class = 'rex_effect_' . $effect_params['effect'];
                    $effect = new $effect_class();
                    $effect->setMedia($this->media);
                    $effect->setParams($effect_params['params']);
                    $effect->execute();
                }
            }
        }

        return $this->media;
    }

    public function effectsFromType($type)
    {
        $qry = '
            SELECT e.*
            FROM ' . rex::getTablePrefix() . 'media_manager_type t, ' . rex::getTablePrefix() . 'media_manager_type_effect e
            WHERE e.type_id = t.id AND t.name="' . $type . '" order by e.priority';

        $sql = rex_sql::factory();
        $sql->setQuery($qry);

        $effects = [];
        foreach ($sql as $row) {
            $effname = $row->getValue('effect');
            $params = $row->getArrayValue('parameters');
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

    public static function mediaUpdated(rex_extension_point $ep)
    {
        self::deleteCache($ep->getParam('filename'));
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

    /*
     * deprecated
     */
    public function getCacheFilename()
    {
        return $this->getSourceCacheFilename();
    }
}
