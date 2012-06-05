<?php

class rex_media_manager
{
  private $media_cacher,
  $cache_path,
  $type,
  $use_cache;

  public function __construct(rex_media $media)
  {
    $this->media = $media;
    $this->useCache(true);
  }

  protected function applyEffects($type)
  {
    $this->type = $type;

    $set = array();
    if (!$this->isCached($type)) {
      $set = $this->effectsFromType($type);
      $set = rex_extension::registerPoint('MEDIA_MANAGER_FILTERSET', $set, array('rex_media_type' => $type));

      if (count($set) == 0) {
        return $this->media;
      }

      // execute effects on image
      foreach ($set as $effect_params) {
        $effect_class = 'rex_effect_' . $effect_params['effect'];
        $effect = new $effect_class;
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
      FROM ' . rex::getTablePrefix() . 'media_manager_types t, ' . rex::getTablePrefix() . 'media_manager_type_effects e
      WHERE e.type_id = t.id AND t.name="' . $type . '" order by e.prior';

    $sql = rex_sql::factory();
    // $sql->debugsql = true;
    $sql->setQuery($qry);

    $effects = array();
    foreach ($sql as $row) {
      $effname = $row->getValue('effect');
      $params = json_decode($row->getValue('parameters'), true);
      $effparams = array();

      // extract parameter out of array
      if (isset($params['rex_effect_' . $effname])) {
        foreach ($params['rex_effect_' . $effname] as $name => $value) {
          $effparams[str_replace('rex_effect_' . $effname . '_', '', $name)] = $value;
          unset($effparams[$name]);
        }
      }

      $effect = array(
        'effect' => $effname,
        'params' => $effparams,
      );

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

      // file exists?
      if (file_exists($mediapath)) {
        $filetime = filectime($mediapath);
      } else {
        $media->sendError('Missing original file for cache-validation!');
        exit();
      }
      // cache is newer?
      if ($cachetime > $filetime) {
        return true;
      }
    }

    return false;
  }

  public function getCacheFilename()
  {
    $cacheParams = md5(serialize($this->type . $this->media->getMediapath()));
    return $this->cache_path . 'media_manager__' . $cacheParams . '_' . $this->media->getMediaFilename();

  }

  public function getHeaderCacheFilename()
  {
    return $this->getCacheFilename() . '_header';
  }

  static public function deleteCacheByType($type_id)
  {
    $qry = 'SELECT * FROM ' . rex::getTablePrefix() . 'media_manager_types' . ' WHERE id=' . $type_id;
    $sql = rex_sql::factory();
    //  $sql->debugsql = true;
    $sql->setQuery($qry);
    $counter = 0;
    foreach ($sql as $row) {
      $counter += self::deleteCache(null, $row->getValue('name'));
    }
    return $counter;
  }

  static public function deleteCache($filename = null, $cacheParams = null)
  {
    if (!$filename) {
      $filename = '*';
    }

    if (!$cacheParams) {
      $cacheParams = '*';
    }

    $folders = array();
    $folders[] = rex_path::addonCache('media_manager');
    $folders[] = rex_path::media();

    $counter = 0;
    foreach ($folders as $folder) {
      $glob = glob($folder . 'media_manager__' . $cacheParams . '_' . $filename);
      if ($glob) {
        foreach ($glob as $file) {
          if (rex_file::delete($file)) {
            $counter++;
          }
        }
      }
    }

    return $counter;
  }

  public function sendMedia()
  {
    $headerCacheFilename = $this->getHeaderCacheFilename();
    $CacheFilename = $this->getCacheFilename();

    $header = array();
    if ($this->isCached()) {
      $header = unserialize(file_get_contents($headerCacheFilename));
      foreach ($header as $t => $c) {
        header($t . ': ' . $c);
      }
      readfile($CacheFilename);

    } else {
      if ($this->use_cache) {
        $this->media->sendMedia($CacheFilename, $headerCacheFilename, 1);

      } else {
        $this->media->sendMedia($CacheFilename, $headerCacheFilename, 0);
      }
    }
    exit;

  }

  static public function getSupportedEffectNames()
  {
    $effectNames = array();
    foreach (self::getSupportedEffects() as $effectClass => $effectFile) {
      $effectNames[] = self::getEffectName($effectFile);
    }
    return $effectNames;
  }

  static public function getSupportedEffects()
  {
    $dirs = array(
      __DIR__ . '/effects/'
    );

    $effects = array();
    foreach ($dirs as $dir) {
      $files = glob($dir . 'effect_*.php');
      if ($files) {
        foreach ($files as $file) {
          $effects[self::getEffectClass($file)] = $file;
        }
      }
    }
    return $effects;
  }

  static private function getEffectName($effectFile)
  {
    return str_replace(
      array('effect_', '.php'),
      '',
      basename($effectFile)
    );
  }

  static private function getEffectClass($effectFile)
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
  static public function mediaUpdated($params)
  {
    self::deleteCache($params['filename']);
  }

  static public function init()
  {
    //--- handle image request
    $rex_media_manager_file = self::getMediaFile();
    $rex_media_manager_type = self::getMediaType();

    if ($rex_media_manager_file != '' && $rex_media_manager_type != '') {
      $media_path    = rex_path::media($rex_media_manager_file);
      $cache_path    = rex_path::addonCache('media_manager');

      $media         = new rex_managed_media($media_path);
      $media_manager = new self($media);
      $media_manager->setCachePath($cache_path);
      $media_manager->applyEffects($rex_media_manager_type);
      $media_manager->sendMedia();

      exit();

    }
  }

  static public function getMediaFile()
  {
    $rex_media_file = rex_get('rex_media_file', 'string');
    $rex_media_file = basename($rex_media_file);
    return $rex_media_file;
  }

  static public function getMediaType()
  {
    return rex_get('rex_media_type', 'string');
  }

}
