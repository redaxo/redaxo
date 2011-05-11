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
  	global $REX;

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
    global $REX;

    $qry = '
      SELECT e.*
      FROM '. $REX['TABLE_PREFIX'].'media_manager_types t, '. $REX['TABLE_PREFIX'].'media_manager_type_effects e
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

  /**
   * Returns a rex_image instance representing the image $rex_img_file
   * in respect to $rex_img_type.
   * If the result is not cached, the cache will be created.
   */
   /*
  static public function getMediaCache($rex_media_file, $rex_media_type)
  {
    global $REX;

    $media_path = rex_path::media($rex_media_file, rex_path::RELATIVE);
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

    /**
   * deletes all cache files for the given filename.
   * if not filename is provided all cache files are cleared.
   *
   * Returns the number of cachefiles which have been removed.
   *
   * @param $filename
   */
  static public function deleteCache($filename = null, $cacheParams = null)
  {
    global $REX;

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
    $folders[] = rex_path::media('', rex_path::RELATIVE);

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


}
