<?php

class rex_media {

  private

  $media_path = "",
  $asImage = FALSE,
  $gifsupport = FALSE,
  $img,
  $header = array();

  public function __construct($media_path)
  {
    $this->setMediapath($media_path);
  }

  public function getMediapath()
  {
    return $this->media_path;
  }

  public function setMediapath($media_path)
  {
    // ----- check params
    if (!file_exists($media_path))
    {
      $media_path = rex_path::addon("media_manager","")."media/warning.jpg";
    }
    $this->media_path = $media_path;
    $this->media = basename($media_path);
    $this->setHeader('Content-Disposition','inline; filename="'. $this->getMediaFilename() .'"');
    $this->asImage = FALSE;
  }

  public function getMediaFilename()
  {
    return $this->media;
  }

  public function setHeader($type,$content)
  {
    $this->header[$type] = $content;
  }

  public function asImage()
  {

    if($this->asImage)
    {
      return;
    }

    $this->asImage = TRUE;

    $this->image = array();
    $this->image['format'] = strtoupper(rex_file::extension($this->getMediapath()));

    $this->image['src'] = false;

    if ($this->image['format'] == 'JPG' || $this->image['format'] == 'JPEG')
    {
      $this->image['format'] = 'JPEG';
      $this->image['quality'] = rex_config::get('media_manager', 'jpg_quality', 80);
      $this->image['src'] = @imagecreatefromjpeg($this->getMediapath());

    }elseif ($this->image['format'] == 'PNG')
    {
      $this->image['src'] = @imagecreatefrompng($this->getMediapath());

    }elseif ($this->image['format'] == 'GIF' && $this->hasGifSupport())
    {
      $this->image['src'] = @imagecreatefromgif($this->getMediapath());

    }elseif ($this->image['format'] == 'WBMP')
    {
      $this->image['src'] = @imagecreatefromwbmp($this->getMediapath());

    }else
    {
      $this->image['src'] = @imagecreatefrompng($this->getMediapath());
      $this->image['format'] == 'PNG';
    }

    $this->setHeader('Content-Type','image/' . $this->image['format']);

    if (!$this->image['src'])
    {
      $this->setMediapath(rex_path::addon("media_manager","")."media/warning.jpg");
      $this->asImage();

    }else
    {
      $this->refreshImageDimensions();

    }

  }

  public function refreshImageDimensions()
  {
    $this->image['width'] = imagesx($this->image['src']);
    $this->image['height'] = imagesy($this->image['src']);
  }

  public function hasGifSupport()
  {
    if(function_exists('imagegif'))
    {
      return TRUE;
    }else
    {
      return FALSE;
    }
  }

  public function getFormat()
  {
    return $this->image['format'];
  }

  public function setFormat($format)
  {
    $this->image['format'] = $format;
  }

  public function getImageWidth()
  {
    return $this->image['format'];
  }

  public function getImageHeight()
  {
    return $this->image['height'];
  }




  public function sendMedia($headerCacheFilename, $sourceCacheFilename, $save = FALSE)
  {

    // lastModified

    if($this->asImage)
    {
      $src = $this->getImageSource($sourceCacheFilename, $save = FALSE);
    }else
    {
      $src = file_get_contents($this->getMediapath());
    }
    $this->setHeader("Content-Length", strlen($src));
    ob_end_clean();
    $this->sendHeader($headerCacheFilename,$save);
    echo $src;

    // rex_response::sendResource($content, false, $lastModified);

  }



  public function getImageSource()
  {
    ob_start();

    // TODOS.. die Bildtypen einsetzen

    imagepng($this->image['src']);
    $content = ob_get_clean();
    return $content;

  }

  public function sendHeader($headerCacheFilename, $save = FALSE)
  {
    foreach($this->header as $t => $c)
    {
      header($t.': '.$c);
    }
  }

  public function sendImageSource($headerCacheFilename, $save = FALSE)
  {

  }












  protected function _sendMedia($saveToFileName = null, $lastModified = null)
  {
    $file = $this->img["media_path"];

    if(!$lastModified)
    {
      $lastModified = time();
    }

    // ----- EXTENSION POINT
    $sendfile = TRUE;
    $sendfile = rex_extension::registerPoint('MEDIA_SEND', $sendfile,
    array (
        'img' => $this->img,
        'file' => $this->img["file"],
        'lastModified' => $lastModified,
        'media_path' => $this->img["media_path"]
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
    @chmod($saveToFileName, rex::getFilePerm());

    return TRUE;
  }

  protected function sendError($message, $file = null)
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





  // Image methods

  public function getImage()
  {
    return $this->image['src'];
  }

  public function setImage($src)
  {
    $this->image['src'] = $src;
  }

  public function getWidth()
  {
    return $this->image['width'];
  }

  public function getHeight()
  {
    return $this->image['height'];
  }





  /*
   protected function sendErrorImage($file = null)
   {
   if(!$file)
   $file = dirname(__FILE__).'/../media/warning.jpg';

   // ----- EXTENSION POINT
   $sendfile = TRUE;
   $sendfile = rex_extension::registerPoint('IMAGE_ERROR_SEND', $sendfile,
   array (
   'img' => $this->img,
   'file' => $file,
   )
   );

   if(!$sendfile)
   return FALSE;

   $this->sendHeader(array("Content-Length" => filesize($file)));

   // error image nicht cachen
   header('Cache-Control: false');
   header('HTTP/1.0 404 Not Found');
   header('Content-Length: ' . filesize($file));
   readfile($file);
   }
   */

  /*
   * Static Method: Returns True, if the given image is a valid rex_image
   */
  /*
   static public function isValidImage($media)
   {
   return is_object($media) && is_a($media, 'rex_media');
   }
   */

}
