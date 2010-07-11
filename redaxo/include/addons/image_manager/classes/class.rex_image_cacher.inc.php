<?php

class rex_image_cacher
{
	var $cache_path;

	function rex_image_cacher($cache_path)
	{
		global $REX;
		
		$this->cache_path = $cache_path;
	}
	
	/*public*/ function isCached(/*rex_image*/ $image, $cacheParams)
  {
    if(!rex_image::isValid($image))
    {
      trigger_error('Given image is not a valid rex_image', E_USER_ERROR);
    }
    
    $cache_file = $this->getCacheFile($image, $cacheParams);
    
    // ----- check for cache file
    if (file_exists($cache_file))
    {
      // time of cache
      $cachetime = filectime($cache_file);
      $imagepath = $image->getFilePath();

      // file exists?
      if (file_exists($imagepath))
      {
        $filetime = filectime($imagepath);
      }
      else
      {
        $image->sendError('Missing original file for cache-validation!');
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
  
  /**
   * Returns a rex_image instance representing the cached image.
   * This Method requires a already cached file.
   * 
   * Use rex_image_manager::getImageCache() if the cache should be created if needed.
   */
  /*public*/ function getCachedImage($filename, $cacheParams)
  {
    $cacheFile = $this->_getCacheFile($filename, $cacheParams);
    $rex_image = new rex_image($cacheFile);
    $rex_image->prepare();
    return $rex_image;
  }
  
  /*public*/ function getCacheFile(/*rex_image*/ $image, $cacheParams)
  {
    return $this->_getCacheFile($image->getFileName(), $cacheParams);
  }
  
  /*protected*/ function _getCacheFile($filename, $cacheParams)
  {
    if(!is_string($cacheParams))
    {
      $cacheParams = md5(serialize($cacheParams));
    }
    return $this->cache_path .'image_manager__'. $cacheParams .'_'. $filename;
  }
	
  /*public*/ function sendImage(/*rex_image*/ $image, $cacheParams, $lastModified = null)
	{
    if(!rex_image::isValid($image))
    {
      trigger_error('Given image is not a valid rex_image', E_USER_ERROR);
    }
    
	  // caching gifs doesn't work
//	  if($image->getFormat() == 'GIF' && !$image->hasGifSupport())
//	  {
//	    $image->prepare();
//	    $image->send($lastModified);
//	  }
//	  else
//	  {
	    $cacheFile = $this->getCacheFile($image, $cacheParams);
	    
  	  // save image to file
  	  if(!$this->isCached($image, $cacheParams))
  	  {
  	    $image->prepare();
  	    $image->save($cacheFile);
  	  }
  	  
  	  // send file
      $image->sendHeader();
      readfile($cacheFile);
//	  }
	}
	
  /*
   * Static Method: Returns True, if the given cacher is a valid rex_image_cacher
   */
  /*public static*/ function isValid($cacher)
  {
    return is_object($cacher) && is_a($cacher, 'rex_image_cacher');
  }
  
  /**
	 * deletes all cache files for the given filename.
	 * if not filename is provided all cache files are cleared.
	 * 
	 * Returns the number of cachefiles which have been removed. 
	 * 
	 * @param $filename
	 */
	function deleteCache($filename = null, $cacheParams = null)
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
		$folders[] = $REX['INCLUDE_PATH'] . '/generated/files/';
		$folders[] = $REX['HTDOCS_PATH'] . 'files/';

		$counter = 0;
		foreach($folders as $folder)
		{
			$glob = glob($folder .'image_manager__'. $cacheParams . '_'. $filename);
			if($glob)
			{
				foreach ($glob as $file)
				{
					if(unlink($file))
					{
						$counter++;
					}
				}
			}
		}

		return $counter;
	}
}
