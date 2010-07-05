<?php

class rex_effect_abstract
{
  var $image = array(); // rex_image
  var $params = array(); // effekt parameter

  function setImage(&$image)
  {
    if(!rex_image::isValid($image))
    {
      trigger_error('Given image is not a valid rex_image_abstract', E_USER_ERROR);
    }
    $this->image = &$image;
  }

  function setParams($params)
  {
    $this->params = $params;
  }

  function execute()
  {
    // exectute effect on $this->img
  }

  function getParams()
  {
    // returns an array of parameters which are required for the effect
  }

  /**
   * Allgemeine Hilfsfunktion um nach groessenaenderungen o.ae. die Transparenz eines Bildes zu erhalten
   */
  function keepTransparent($des)
  {
    $image = $this->image;
    if ($image->getFormat() == 'PNG')
    {
      imagealphablending($des, false);
      imagesavealpha($des, true);
    }
    else if ($image->getFormat() == 'GIF')
    {
      $gdimage =& $image->getImage();
      $colorTransparent = imagecolortransparent($gdimage);
      imagepalettecopy($gdimage, $des);
      if($colorTransparent>0)
      {
        imagefill($des, 0, 0, $colorTransparent);
        imagecolortransparent($des, $colorTransparent);
      }
      imagetruecolortopalette($des, true, 256);
    }
  }
}