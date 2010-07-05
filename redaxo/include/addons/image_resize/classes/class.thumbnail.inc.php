<?php


/**
 * Image-Resize Addon
 *
 * @author office[at]vscope[dot]at Wolfgang Hutteger
 * @author markus[dot]staab[at]redaxo[dot]de Markus Staab
 * @author jan[dot]kristinus[at]redaxo[dot]de Jan Kristinus
 *
 *
 * @package redaxo4
 * @version svn:$Id$
 */

class rex_thumbnail
{
  var $img;
  var $gifsupport;
  var $imgfile;
  var $filters;
  var $warning_image;
  var $img_filename;
  var $img_cachepath;

  function rex_thumbnail($imgfile)
  {
    global $REX;

    // ----- imagepfad speichern
    $this->img = array();
    $this->imgfile = $imgfile;

    // ----- gif support ?
    $this->gifsupport = function_exists('imageGIF');

    // ----- detect image format
    $this->img['format'] = strtoupper(OOMedia::_getExtension($imgfile));
    $this->img['src'] = false;
    
    if (strpos($imgfile, 'cache/') === false)
    {
      if ($this->img['format'] == 'JPG' || $this->img['format'] == 'JPEG')
      {
        // --- JPEG
        $this->img['format'] = 'JPEG';
        $this->img['src'] = @ImageCreateFromJPEG($imgfile);
      }elseif ($this->img['format'] == 'PNG')
      {
        // --- PNG
        $this->img['src'] = @ImageCreateFromPNG($imgfile);
      }elseif ($this->img['format'] == 'GIF')
      {
        // --- GIF
        if ($this->gifsupport)
          $this->img['src'] = @ImageCreateFromGIF($imgfile);
      }elseif ($this->img['format'] == 'WBMP')
      {
        // --- WBMP
        $this->img['src'] = @ImageCreateFromWBMP($imgfile);
      }

      // ggf error image senden
      if (!$this->img['src'])
      {
        $this->sendError();
        exit();
      }

      $this->img['width'] = imagesx($this->img['src']);
      $this->img['height'] = imagesy($this->img['src']);
      $this->img['width_offset_thumb'] = 0;
      $this->img['height_offset_thumb'] = 0;

      // --- default quality jpeg
      $this->img['quality'] = $REX['ADDON']['image_resize']['jpg_quality'];
      $this->filters = array();
    }
  }

  function size_height($size)
  {
    // --- height
    $this->img['height_thumb'] = (int) $size;
    // siehe http://forum.redaxo.de/ftopic9292.html
    if ($this->img['width_thumb'] == 0)
    {
      $this->img['width_thumb']  = (int) ($this->img['height_thumb'] / $this->img['height'] * $this->img['width']);
    }
  }

  function size_width($size)
  {
    // --- width
    $this->img['width_thumb']  = (int) $size;
    $this->img['height_thumb'] = (int) ($this->img['width_thumb'] / $this->img['width'] * $this->img['height']);
  }

  function size_auto($size)
  {
    // --- size
    if ($this->img['width'] >= $this->img['height'])
    {
      $this->size_width($size);
    }
    else
    {
      $this->size_height($size);
    }
  }

  /**
   * Ausschnitt aus dem Bild auf bestimmte größe zuschneiden
   *
   * @param $width int Breite des Ausschnitts
   * @param $height int Hoehe des Ausschnitts
   * @param $offset int Verschiebung des Ausschnitts vom Zentrum ausgehend
   */
  function size_crop($width, $height, $offset)
  {
    $this->img['width_thumb']  = (int) $width;
    $this->img['height_thumb'] = (int) $height;

    $width_ratio = $this->img['width'] / $this->img['width_thumb'];
    $height_ratio = $this->img['height'] / $this->img['height_thumb'];

    // Es muss an der Breite beschnitten werden
    if ($width_ratio > $height_ratio)
    {
      $this->img['width_offset_thumb'] = (int) (round(($this->img['width'] - $this->img['width_thumb'] * $height_ratio) / 2) + $offset);
      $this->img['width']              = (int) round($this->img['width_thumb'] * $height_ratio);
    }
    // es muss an der Höhe beschnitten werden
    elseif ($width_ratio < $height_ratio)
    {
      $this->img['height_offset_thumb'] = (int) (round(($this->img['height'] - $this->img['height_thumb'] * $width_ratio) / 2) + $offset);
      $this->img['height']              = (int) round($this->img['height_thumb'] * $width_ratio);
    }
  }

  function jpeg_quality($quality = 85)
  {
    // --- jpeg quality
    $this->img['quality'] = $quality;
  }

  function resampleImage()
  {
    // Originalbild selbst sehr klein und wuerde via resize vergroessert
    // => Das Originalbild ausliefern
    if($this->img['width_thumb'] > $this->img['width'] &&
       $this->img['height_thumb'] > $this->img['height'])
    {
      $this->img['width_thumb'] = $this->img['width'];
      $this->img['height_thumb'] = $this->img['height'];
    }

    if (function_exists('ImageCreateTrueColor'))
    {
      $this->img['des'] = @ImageCreateTrueColor($this->img['width_thumb'], $this->img['height_thumb']);
    }
    else
    {
      $this->img['des'] = @ImageCreate($this->img['width_thumb'], $this->img['height_thumb']);
    }

    if(!$this->img['des'])
    {
      $this->sendError();
      exit();
    }

    // Transparenz erhalten
    $this->keepTransparent($this->img['des']);
    imagecopyresampled($this->img['des'], $this->img['src'], 0, 0, $this->img['width_offset_thumb'], $this->img['height_offset_thumb'], $this->img['width_thumb'], $this->img['height_thumb'], $this->img['width'], $this->img['height']);
  }
  
  function keepTransparent($destImage)
  {
    if ($this->img['format'] == 'PNG')
    {
      imagealphablending($destImage, false);
      imagesavealpha($destImage, true);
    }
    else if ($this->img['format'] == 'GIF')
    {
      $colorTransparent = imagecolortransparent($this->img['src']);
      imagepalettecopy($this->img['src'], $destImage);
      if($colorTransparent>0)
      {
      	imagefill($destImage, 0, 0, $colorTransparent);
      	imagecolortransparent($destImage, $colorTransparent);
      }
      imagetruecolortopalette($destImage, true, 256);
    }
  }

  function generateImage($file = null, $show = true)
  {
    global $REX;

    if ($this->img['format'] == 'GIF' && !$this->gifsupport)
    {
      // --- kein caching -> gif ausgeben
      $this->send();
    }

    $this->resampleImage();
    $this->applyFilters();
		$this->checkCacheFiles();

    if ($this->img['format'] == 'JPG' || $this->img['format'] == 'JPEG')
    {
      imageJPEG($this->img['des'], $file, $this->img['quality']);
    }
    elseif ($this->img['format'] == 'PNG')
    {
      imagePNG($this->img['des'], $file);
    }
    elseif ($this->img['format'] == 'GIF')
    {
      imageGIF($this->img['des'], $file);
    }
    elseif ($this->img['format'] == 'WBMP')
    {
      imageWBMP($this->img['des'], $file);
    }

    if($file)
      @chmod($file, $REX['FILEPERM']);

    if ($show)
    {
      $this->send($file);
    }
  }

	function checkCacheFiles()
	{
		global $REX;
		$glo = glob($this->img_cachepath."image_resize__*"."__".$this->img_filename);
		if ($REX['ADDON']['image_resize']['max_cachefiles']<=count($glo))
		{
			$cachefile = '';
			$cachetime = -1;

			// nur das Šlteste Cachefile lšschen
			foreach($glo as $gl)
			{
				if ($cachetime == -1 || filectime($gl) < $cachetime)
				{
					$cachetime = filectime($gl);
					$cachefile = $gl;
				}
			}
			if ($cachefile != "") unlink ($cachefile);
		}
	}

  function send($file = null, $lastModified = null)
  {
    if (!$file)
      $file = $this->imgfile;
    if (!$lastModified)
      $lastModified = time();

    $lastModified =  gmdate('D, d M Y H:i:s', $lastModified).' GMT';

		// ----- EXTENSION POINT
		$sendfile = TRUE;
    $sendfile = rex_register_extension_point('IMAGE_RESIZE_SEND', $sendfile,
      array (
      	'img' => $this->img,
        'file' => $file,
        'lastModified' => $lastModified,
        'filename' => $this->img_filename
      )
    );

		if(!$sendfile)
			return FALSE;
			
    if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $lastModified)
    {
      header('HTTP/1.1 304 Not Modified');
      exit();
    }
	
    header('Content-Disposition: inline; filename="'. $this->img_filename .'"');
    header('Content-Type: image/' . $this->img['format']);
    header('Last-Modified: ' . $lastModified);
    // caching clientseitig/proxieseitig erlauben
    header('Cache-Control: public');



    readfile($file);
  }

  function sendError($file = null)
  {
    global $REX;

    if(!$file)
      $file = $REX['INCLUDE_PATH'].'/addons/image_resize/media/warning.jpg';

		// ----- EXTENSION POINT
		$sendfile = TRUE;
    $sendfile = rex_register_extension_point('IMAGE_RESIZE_SENDERROR', $sendfile,
      array (
      	'img' => $this->img,
        'file' => $file,
      )
    );

		if(!$sendfile)
			return FALSE;


    header('Content-Type: image/JPG');
    // error image nicht cachen
    header('Cache-Control: false');
    header('HTTP/1.0 404 Not Found');
    readfile($file);
    exit();
  }

  function addFilter($filter)
  {
  	global $REX;
  	if ($filter == "") return;
    $this->filters[] = $filter;
  }

  function applyFilters()
  {
  	global $REX;

  	foreach($this->filters as $filter)
  	{
  	  $filter = preg_replace('[^a-zA-Z0-9\_]', '', $filter);
  		$file = $REX['INCLUDE_PATH'].'/addons/image_resize/filters/filter.'.$filter.'.inc.php';
  		if (file_exists($file)) require_once($file);
  		$fname = 'image_resize_'.$filter;
  		if (function_exists($fname))
  		{
  			$fname($this->img['des']);
  		}
  	}
  }

  // deleteCache
  function deleteCache($filename = '')
  {
  	global $REX;

	  $folders = array();
    $folders[] = $REX['INCLUDE_PATH'] . '/generated/files/';
    $folders[] = $REX['HTDOCS_PATH'] . 'files/';

  	$c = 0;
    foreach($folders as $folder)
    {
      $glob = glob($folder .'image_resize__*');
      if($glob)
      {
  	    foreach ($glob as $var)
  	    {
        	if ($filename == '' || $filename != '' && $filename == substr($var,strlen($filename) * -1))
        	{
        		unlink($var);
        		$c++;
        	}
  	    }
      }
    }

	  return $c;
  }

  function getImage()
  {
    return $this->img['src'];
  }

  function getImageWidth()
  {
    return $this->img['width'];
  }

  function getImageHeight()
  {
    return $this->img['height'];
  }

  function destroyImage()
  {
    imagedestroy($this->getImage());
  }

  function createFromUrl($rex_resize)
  {
    global $REX;

	  // Loesche alle Ausgaben zuvor
		while(ob_get_level())
		  ob_end_clean();

		$rex_resize = str_replace("/","",$rex_resize);
		  
    // get params
    preg_match('@([0-9]+)([awhc])__(([0-9]+)h__)?((\-?[0-9]+)o__)?(.*)@', $rex_resize, $resize);
    
	  $size = $resize[1];
	  $mode = $resize[2];
	  $height = $resize[4];
    $offset = $resize[6];
	  $imagefile = $resize[7];
	  $rex_filter = rex_get('rex_filter', 'array');

	  if (count($rex_filter)>$REX['ADDON']['image_resize']['max_filters']) 
	    $rex_filter = array();

	  $filters = '';
		foreach($rex_filter as $filter)
			$filters .= $filter;

	  if($filters != '')
		  $filters = md5($filters);

	  $cachepath = $REX['INCLUDE_PATH'].'/generated/files/image_resize__'.$filters.$rex_resize;
	  $imagepath = $REX['HTDOCS_PATH'].'files/'.$imagefile;

	  // ----- check for cache file
	  if (file_exists($cachepath))
	  {
	    // time of cache
	    $cachetime = filectime($cachepath);

	    // file exists?
	    if (file_exists($imagepath))
	    {
	      $filetime = filectime($imagepath);
	    }
	    else
	    {
	      // image file not exists
	      print 'Error: Imagefile does not exist - '. $imagefile;
	      exit;
	    }
	    // cache is newer? - show cache
      if ($cachetime > $filetime)
	    {
	      $thumb = new rex_thumbnail($cachepath);
	      $thumb->img_filename = $imagefile;
	      $thumb->send($cachepath, $cachetime);
	      exit;
	    }

	  }

	  // ----- check params
	  if (!file_exists($imagepath))
	  {
	    print 'Error: Imagefile does not exist - '. $imagefile;
	    exit;
	  }

		// ----- check filesize
		$max_file_size = $REX['ADDON']['image_resize']['max_resizekb']*1024;
		if (filesize($imagepath)>$max_file_size)
		{
	    print 'Error: Imagefile is to big. Only files < '.$REX['ADDON']['image_resize']['max_resizekb'].'kb are allowed. - '. $imagefile;
	    exit;
		}

		// ----- check mode
	  if (($mode != 'w') && ($mode != 'h') && ($mode != 'a') && ($mode != 'c'))
	  {
	    print 'Error wrong mode - only h,w,a,c';
	    exit;
	  }

	  if ($size == '')
	  {
	    print 'Error size is no INTEGER';
	    exit;
	  }

	  if ($size > $REX['ADDON']['image_resize']['max_resizepixel'] || $height > $REX['ADDON']['image_resize']['max_resizepixel'])
	  {
	    print 'Error size to big: max '.$REX['ADDON']['image_resize']['max_resizepixel'].' px';
	    exit;
	  }

	  // ----- start thumb class
	  $thumb = new rex_thumbnail($imagepath);

	  $thumb->img_filename = $imagefile;
  	$thumb->img_cachepath = $REX['INCLUDE_PATH'].'/generated/files/';

	  // check method
	  if ($mode == 'w')
	  {
	    $thumb->size_width($size);
	  }
	  if ($mode == 'h')
	  {
	    $thumb->size_height($size);
	  }

	  if ($mode == 'c')
	  {
	    $thumb->size_crop($size, $height, $offset);
	  }elseif ($height != '')
	  {
	    $thumb->size_height($height);
	  }

	  if ($mode == 'a')
	  {
	    $thumb->size_auto($size);
	  }

	  // Add Default Filters
	  $rex_filter = array_merge($rex_filter,$REX['ADDON']['image_resize']['default_filters']);

	  // Add Filters
	  foreach($rex_filter as $filter)
	  {
	    $thumb->addFilter($filter);
	  }

	  // save cache
	  $thumb->generateImage($cachepath);
	  exit ();
	}
}