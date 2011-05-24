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
    $this->useCache(TRUE);
  }

  function applyEffects($type)
  {
    $this->type = $type;

    $set = array();
    if(!$this->isCached($type))
    {
      $set = $this->effectsFromType($type);
      $set = rex_extension::registerPoint('MEDIA_MANAGER_FILTERSET',$set,array('rex_media_type'=>$type));

      if(count($set) == 0)
      {
        return $this->media;
      }

      // execute effects on image
      foreach($set as $effect_params)
      {
        $effect_class = 'rex_effect_'.$effect_params['effect'];
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
      FROM '. rex::getTablePrefix().'media_manager_types t, '. rex::getTablePrefix().'media_manager_type_effects e
      WHERE e.type_id = t.id AND t.name="'. $type .'" order by e.prior';

    $sql = rex_sql::factory();
    // $sql->debugsql = true;
    $sql->setQuery($qry);

    $effects = array();
    foreach($sql as $row)
    {
      $effname = $row->getValue('effect');
      $params = json_decode($row->getValue('parameters'), true);
      $effparams = array();

      // extract parameter out of array
      if(isset($params['rex_effect_'. $effname]))
      {
        foreach($params['rex_effect_'. $effname] as $name => $value)
        {
          $effparams[str_replace('rex_effect_'. $effname .'_', '', $name)] = $value;
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





  /* ************ CACHEFUNKTION */

  public function setCachePath($cache_path = "")
  {
    $this->cache_path = $cache_path;

  }

  public function getCachePath()
  {
    return $this->cache_path;

  }

  function useCache($t = TRUE)
  {
    $this->use_cache = $t;
  }

  public function isCached()
  {
    $cache_file = $this->getCacheFile();

    // ----- check for cache file
    if (1==2 && file_exists($cache_file))
    {
      // time of cache
      $cachetime = filectime($cache_file);
      $mediapath = $media->getMediaPath();

      // file exists?
      if (file_exists($mediapath))
      {
        $filetime = filectime($mediapath);
      }
      else
      {
        $media->sendError('Missing original file for cache-validation!');
        exit();
      }
      // cache is newer?
      if ($cachetime > $filetime)
      {
        return true;
      }
    }

    return false;
  }

  public function getCacheFile()
  {
    $cacheParams = md5(serialize($this->type));
    return $this->cache_path .'media_manager__'. $cacheParams .'_'. $this->media->getMediaFilename();

  }

  public function getHeaderCacheFile()
  {
    return $this->getCacheFile().'_header';
  }

  public function deleteCacheByType($type_id)
  {
    $qry = 'SELECT * FROM '. rex::getTablePrefix().'media_manager_types' . ' WHERE id='. $type_id;
    $sql = rex_sql::factory();
    //  $sql->debugsql = true;
    $sql->setQuery($qry);

    $counter = 0;
    foreach($sql as $row)
    {
      $counter += rex_media_manager::deleteCache(null, $row->getValue('name'));
    }

    return $counter;
  }


  /**
   * Returns a rex_image instance representing the image $rex_img_file
   * in respect to $rex_img_type.
   * If the result is not cached, the cache will be created.
   */
  /*
   static public function getMediaCache($rex_media_file, $rex_media_type)
   {
   $media_path = rex_path::media($rex_media_file);
   $cache_path = rex_path::cache('media/');

   $media         = new rex_media($media_path);
   $media_cacher  = new rex_media_manager_cacher($cache_path);

   // create image with given image_type if needed
   if(!$media_cacher->isCached($media, $rex_media_type))
   {
   $media_manager = new rex_media_manager($media_cacher);
   $media_manager->applyEffects($media, $rex_media_type);
   $media->save($media_cacher->getCacheFile($media, $rex_media_type));
   }

   return $media_cacher->getCachedImage($rex_media_file, $rex_media_type);
   }
   */

  static public function deleteCache($filename = null, $cacheParams = null)
  {
    if(!$filename)
    {
      $filename = '*';
    }

    if(!$cacheParams)
    {
      $cacheParams = '*';
    }

    $folders = array();
    $folders[] = rex_path::cache('media/');
    $folders[] = rex_path::media();

    $counter = 0;
    foreach($folders as $folder)
    {
      $glob = glob($folder .'media_manager__'. $cacheParams . '_'. $filename);
      if($glob)
      {
        foreach ($glob as $file)
        {
          if(rex_file::delete($file))
          {
            $counter++;
          }
        }
      }
    }

    return $counter;
  }



  public function sendMedia()
  {
    $header = array();
    if($this->isCached())
    {

      // header auslesen und ausgeben

      // src auslesen und ausgeben

      echo "gecachte version senden";
      echo "geachten header verwenden";

      foreach($header as $h) { header($h); }
      readfile($filepath);


    }else
    {
      if($this->use_cache)
    		$this->media->sendMedia($this->getCacheFile(), $this->getHeaderCacheFile(),1);
    		else
    		$this->media->sendMedia($this->getCacheFile(), $this->getHeaderCacheFile(),0);

    }


    exit;
  }





  public function getSupportedEffectNames()
  {
    $effectNames = array();
    foreach(rex_media_manager::getSupportedEffects() as $effectClass => $effectFile)
    {
      $effectNames[] = rex_media_manager::getEffectName($effectFile);
    }
    return $effectNames;
  }

  function getSupportedEffects()
  {
    $dirs = array(
    dirname(__FILE__). '/../lib/effects/'
    );

    $effects = array();
    foreach($dirs as $dir)
    {
      $files = glob($dir . 'class.rex_effect_*.inc.php');
      if($files)
      {
        foreach($files as $file)
        {
          $effects[rex_media_manager::getEffectClass($file)] = $file;
        }
      }
    }
    return $effects;
  }

  function getEffectName($effectFile)
  {
    return str_replace(
    array('class.rex_effect_', '.inc.php'),
      '',
    basename($effectFile)
    );
  }

  function getEffectClass($effectFile)
  {
    return str_replace(
    array('class.', '.inc.php'),
      '',
    basename($effectFile)
    );
  }







  /*
   * For ExtensionPoints.
   */
  function mediaUpdated($params){
    rex_media_manager::deleteCache($params["filename"]);
  }

  function init()
  {
    //--- handle image request
    $rex_media_manager_file = rex_media_manager::getMediaFile();
    $rex_media_manager_type = rex_media_manager::getMediaType();

    if($rex_media_manager_file != '' && $rex_media_manager_type != '')
    {

      
      
      $media_path    = rex_path::media($rex_media_manager_file);
      $cache_path    = rex_path::cache('media/');

      $media         = new rex_media($media_path);

      $media_manager = new rex_media_manager($media); // $media_manager_cacher
      $media_manager->setCachePath($cache_path);
      $media_manager->applyEffects($rex_media_manager_type);
      $media_manager->sendMedia();

      exit();

    }
  }

  static function getMediaFile()
  {
    $rex_media_file = rex_get('rex_media_file', 'string');
    $rex_media_file = basename($rex_media_file);
    return $rex_media_file;
  }

  static function getMediaType()
  {
    return rex_get('rex_media_type', 'string');
  }

}
