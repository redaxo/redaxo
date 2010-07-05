<?php

class rex_effect_mirror extends rex_effect_abstract
{
  var $options;

  function rex_effect_mirror()
  {


  }

  function execute()
  {
    $gdimage =& $this->image->getImage();
    $w = $this->image->getWidth();
    $h = $this->image->getHeight();

    $this->params["gradient"] = (int) $this->params["gradient"];

    if($this->params["gradient"]>99 || $this->params["gradient"] <1 )
    {
      $this->params["gradient"] = 55;
    }
    $this->params["gradient"] = $this->params["gradient"]/100;

    $this->params["shadow"] = (int) $this->params["shadow"];
    if($this->params["shadow"]>99 || $this->params["shadow"] <1 )
    {
      $this->params["shadow"] = 10;
    }
    $this->params["shadow"] = $this->params["shadow"]/100;

    if(!isset($this->params["bg_r"]) || $this->params["bg_r"]>255 || $this->params["bg_r"] <0 )
    {
      $this->params["bg_r"] = 255;
    }

    if(!isset($this->params["bg_g"]) || $this->params["bg_g"]>255 || $this->params["bg_g"] <0 )
    {
      $this->params["bg_g"] = 255;
    }

    if(!isset($this->params["bg_b"]) || $this->params["bg_b"]>255 || $this->params["bg_b"] <0 )
    {
      $this->params["bg_b"] = 255;
    }

    $gdimage = $this->imagereflection ( $gdimage, array ($this->params["bg_r"], $this->params["bg_g"], $this->params["bg_b"]), $this->params["gradient"], $this->params["shadow"] );

    return;

  }

  function getParams()
  {
    global $REX,$I18N;

    return array(
    array(
		        'label'=>$I18N->msg('imanager_effect_mirror_gradient'),
		        'name' => 'gradient',
		        'type' => 'int',
            'default' => 55,
    ),
    array(
		        'label'=>$I18N->msg('imanager_effect_mirror_shadow'),
		        'name' => 'shadow',
		        'type' => 'int',
            'default' => 10,
    ),
    array(
		        'label'=>$I18N->msg('imanager_effect_mirror_background_r'),
		        'name' => 'bg_r',
		        'type' => 'int',
            'default' => 255,
    ),
    array(
		        'label'=>$I18N->msg('imanager_effect_mirror_background_g'),
		        'name' => 'bg_g',
		        'type' => 'int',
            'default' => 255,
    ),
    array(
		        'label'=>$I18N->msg('imanager_effect_mirror_background_b'),
		        'name' => 'bg_b',
		        'type' => 'int',
            'default' => 255,
    ),
    );
  }


  function imagereflection ( &$simg, $background = array (255, 255, 255), $gradient = 0.55, $shadow = 0.1 ) {
    $simgx = imagesx($simg);
    $simgy = imagesy($simg);
    // Hoehen von Verlauf und Schatten in px bestimmen
    $gradientH = round($simgy * $gradient);
    $shadowH   = round($simgy * $shadow);
    // Zielbild erzeugen
    $dimg = imagecreatetruecolor($simgx, $simgy + $gradientH );
    imagealphablending($dimg, false);
    imagesavealpha($dimg, true);
    // und mit Hintergrundfarbe fuellen
    $bgcolor = imagecolorallocate($dimg, $background[0], $background[1], $background[2]);
    imagefill($dimg, 0, 0, $bgcolor);
    // Quellbild kopieren
    imagecopy($dimg, $simg, 0, 0, 0, 0, $simgx, $simgy);
    // und das gespiegelte Bild einfuegen
//    $simg = $this->imageflip($simg, 1);
//    imagecopy($dimg, $simg, 0, $simgy, 0, 0, $simgx, $simgy);

    // Verlauf erzeugen
    $alphaF = 60 / ($gradientH - 1);
    for ($i = 0; $i < $gradientH; $i++) {
      $col = imagecolorallocatealpha($dimg, $background[0], $background[1], $background[2], 60 - $i * $alphaF);
      imageline($dimg, 0, $simgy + $i, $simgx, $simgy + $i, $col);
    }

    // Schatten erzeugen
    $alphaF = 60 / ($shadowH - 1);
    for ($i = 0; $i < $shadowH; $i++) {
      $col = imagecolorallocatealpha($dimg, 160, 160, 160, $i*$alphaF + 67);
      imageline($dimg, 0, $simgy + $i, $simgx, $simgy + $i, $col);
    }
    
    imagealphablending($dimg, false);
    imagesavealpha($dimg, true);

    // Bild zurueckgeben
    return $dimg;
  }

  function imageflip ( $imgsrc, $mode = 3 )
  {
    $width                        =    imagesx ( $imgsrc );
    $height                       =    imagesy ( $imgsrc );
    $src_x                        =    0;
    $src_y                        =    0;
    $src_width                    =    $width;
    $src_height                   =    $height;
    switch ( $mode )
    {
      case '1': //vertical
        $src_y                =    $height -1;
        $src_height           =    -$height;
        break;
      case '2': //horizontal
        $src_x                =    $width -1;
        $src_width            =    -$width;
        break;
      case '3': //both
        $src_x                =    $width -1;
        $src_y                =    $height -1;
        $src_width            =    -$width;
        $src_height           =    -$height;
        break;
      default:
        return $imgsrc;
    }
    $imgdest = imagecreatetruecolor ( $width, $height );

    if ( imagecopyresampled ( $imgdest, $imgsrc, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height ) )
    {
      return $imgdest;
    }
    return $imgsrc;
  }

}