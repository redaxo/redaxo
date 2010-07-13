<?php

class rex_image {

	var $img;
	var $gifsupport = FALSE;

	function rex_image($filepath)
	{
	  global $REX;
	  
    // ----- check params
    if (!file_exists($filepath))
    {
      $this->sendError('Imagefile does not exist - '. $filepath);
      exit();
    }
    
    // ----- check filesize
    $max_file_size = $REX['ADDON']['image_manager']['max_resizekb'] * 1024;
    $filesize = filesize($filepath);
    if ($filesize>$max_file_size)
    {
      $error  = 'Imagefile is to big.';
      $error .= ' Only files < '.$REX['ADDON']['image_manager']['max_resizekb'].'kb are allowed';
      $error .= '- '. $filepath . ', '. OOMedia::_getFormattedSize($filesize);
      $this->sendError($error);
      exit();
    }
    
    // ----- imagepfad speichern
    $this->img = array();
    $this->img['file'] = basename($filepath);
    $this->img['filepath'] = $filepath;
    $this->img['quality'] = $REX['ADDON']['image_manager']['jpg_quality'];
    $this->img['format'] = strtoupper(OOMedia::_getExtension($this->img['filepath']));
	}
	
	/*public*/ function prepare()
	{
	  if(!isset($this->img['src']))
	  {
      // ----- gif support ?
      $this->gifsupport = function_exists('imagegif');
  
      // ----- detect image format
      $this->img['src'] = false;
      if ($this->img['format'] == 'JPG' || $this->img['format'] == 'JPEG')
      {
        // --- JPEG
        $this->img['format'] = 'JPEG';
        $this->img['src'] = @imagecreatefromjpeg($this->img["filepath"]);
      }elseif ($this->img['format'] == 'PNG')
      {
        // --- PNG
        $this->img['src'] = @imagecreatefrompng($this->img["filepath"]);
      }elseif ($this->img['format'] == 'GIF')
      {
        // --- GIF
        if ($this->gifsupport)
          $this->img['src'] = @imagecreatefromgif($this->img["filepath"]);
      }elseif ($this->img['format'] == 'WBMP')
      {
        // --- WBMP
        $this->img['src'] = @imagecreatefromwbmp($this->img["filepath"]);
      }
  
      // ggf error image senden
      if (!$this->img['src'])
      {
        $this->sendError('Unable to create gdressource from file "'.$this->img["filepath"].'"!');
        exit();
      }else
      {
        $this->refreshDimensions();
      }
	  }
	}
	
	/*public*/ function refreshDimensions()
	{
    $this->img['width'] = imagesx($this->img['src']);
    $this->img['height'] = imagesy($this->img['src']);
	}

	/*public*/ function hasGifSupport()
	{
	  return $this->gifsupport;
	}

	/*public*/ function &getImage()
	{
		return $this->img['src'];
	}
	
	/*public*/ function getFormat()
	{
	  return $this->img['format'];
	}
	
  /*public*/ function getFileName()
  {
	  return $this->img['file'];
  }
  
  /*public*/ function getFilePath()
  {
	  return $this->img['filepath'];
  }
  
  /*public*/ function getWidth()
  {
	  return $this->img['width'];
  }
  
  /*public*/ function getHeight()
  {
	  return $this->img['height'];
  }
  
  /*public*/ function destroy()
  {
    imagedestroy($this->img['src']);
  }

  /*public*/ function save($filename)
	{
	  $this->_sendImage($filename);
	}
	
  /*public*/ function send($lastModified = null)
	{
	  ob_start();
    $res = $this->_sendImage(null, $lastModified);
    $content = ob_get_clean();
    
    if(!$res)
      return false;
    
    $this->sendHeader();
    rex_send_resource($content, false, $lastModified);
	}
	
	/*public*/ function sendHeader()
	{
    header('Content-Disposition: inline; filename="'. $this->img['file'] .'"');
    header('Content-Type: image/' . $this->img['format']);
	}
	
	/*protected*/ function _sendImage($saveToFileName = null, $lastModified = null)
	{
		global $REX;
		
    $file = $this->img["filepath"];
    
    if(!$lastModified)
    {
      $lastModified = time();
    }
    
    // ----- EXTENSION POINT
    $sendfile = TRUE;
    $sendfile = rex_register_extension_point('IMAGE_SEND', $sendfile,
      array (
      // TODO Parameter anpassen
          'img' => $this->img,
          'file' => $this->img["file"],
          'lastModified' => $lastModified,
          'filepath' => $this->img["filepath"]
      )
    );

    if(!$sendfile)
      return FALSE;
      
    // output image
    if ($this->img['format'] == 'JPG' || $this->img['format'] == 'JPEG')
    {
      imagejpeg($this->img['src'], $saveToFileName, $this->img['quality']);
    }
    elseif ($this->img['format'] == 'PNG')
    {
      if(isset($saveToFileName))
        imagepng($this->img['src'], $saveToFileName);
      else
        imagepng($this->img['src']);
    }
    elseif ($this->img['format'] == 'GIF')
    {
      imagegif($this->img['src'], $saveToFileName);
    }
    elseif ($this->img['format'] == 'WBMP')
    {
      imagewbmp($this->img['src'], $saveToFileName);
    }
    
    if ($saveToFileName)
      @chmod($saveToFileName, $REX['FILEPERM']);
      
    return TRUE;
	}

	/*protected*/ function sendError($message, $file = null)
	{
	  // User die auch im Backend eingeloggt sind, bekommen eine Fehlermeldung
	  // alle anderen ein ErrorImage
	  if($message != '' && rex_hasBackendSession())
	  {
	    echo 'Error: '. $message;
	    exit();
	  }
	  else
	  {
	    $this->sendErrorImage($file);
	  }
	}
	
	/*protected*/ function sendErrorImage($file = null)
	{
		if(!$file)
  		$file = dirname(__FILE__).'/../media/warning.jpg';

		// ----- EXTENSION POINT
		$sendfile = TRUE;
		$sendfile = rex_register_extension_point('IMAGE_ERROR_SEND', $sendfile,
  		array (
        	'img' => $this->img,
          'file' => $file,
  		)
		);

		if(!$sendfile)
	   	return FALSE;

    $this->sendHeader();
    
		// error image nicht cachen
		header('Cache-Control: false');
		header('HTTP/1.0 404 Not Found');
		
		readfile($file);
	}
	
  /*
   * Static Method: Returns True, if the given image is a valid rex_image
   */
  /*public static*/ function isValid($image)
  {
    return is_object($image) && is_a($image, 'rex_image');
  }
}
