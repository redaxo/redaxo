<?php
/**
 * Branded ein Bild mit einem Wasserzeichen
 *
 * Der Filter sucht im Verzeichnis addons/image_resize/media/
 * nach einem Bild mit dem Dateinamen "brand.*" und verwendet den 1. Treffer
 */
function image_resize_brand(& $src_im)
{
  global $REX;

  $files = glob($REX['INCLUDE_PATH'] . '/addons/image_resize/media/brand.*');
  $brandImage = $files[0];
  $brand = new rex_thumbnail($brandImage);

  // -------------------------------------- CONFIG
  
  // Abstand vom Rand
  $paddX = -10;
  $paddY = -10;
  // horizontale ausrichtung: left/center/right
  $hpos = 'right'; 
  // vertikale ausrichtung:   top/center/bottom
  $vpos = 'bottom';
  
  // -------------------------------------- /CONFIG
  
  switch($hpos)
  {
    case 'left':
      $dstX = 0;
      break;
    case 'center':
      $dstX = (int)((imagesx($src_im) - $brand->getImageWidth()) / 2);
      break;
    case 'right':
      $dstX = imagesx($src_im) - $brand->getImageWidth();
      break;
    default: trigger_error('Unexpected value for "hpos"!', E_USER_ERROR);
  }
  
  switch($vpos)
  {
    case 'top':
      $dstY = 0;
      break;
    case 'center':
      $dstY = (int)((imagesy($src_im) - $brand->getImageHeight()) / 2);
      break;
    case 'bottom':
      $dstY = imagesy($src_im) - $brand->getImageHeight();
      break;
    default: trigger_error('Unexpected value for "vpos"!', E_USER_ERROR);
  }
  
  imagealphablending($src_im, true);
  imagecopy($src_im, $brand->getImage(), $dstX + $paddX, $dstY + $paddY, 0, 0, $brand->getImageWidth(), $brand->getImageHeight());

  $brand->destroyImage();
}