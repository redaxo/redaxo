<?php

/**
 * Spiegel ein Bild
 */

class rex_effect_flip extends rex_effect_abstract
{
  private $options;

  public function __construct()
  {
    $this->options = array(
      'X', 'Y'
    );
  }

  public function execute()
  {
    $this->media->asImage();

    $gdimage = $this->media->getImage();
    $w = $this->media->getWidth();
    $h = $this->media->getHeight();

    $width = imagesx( $gdimage );
    $height = imagesy( $gdimage );
    $output = imagecreatetruecolor( $width, $height );

    // --------------- Flip X
    if ($this->params['flip'] == 'X') {
      $y = 0;
      $x = 1;
      while ( $x <= $width ) {
        for ( $i = 0; $i < $height; $i++ ) {
          imagesetpixel( $output, $x, $i, imagecolorat( $gdimage, ( $width - $x ), ( $i ) ) );
        }
        $x++;
      }
      $this->media->setImage($output);
    }

    // --------------- Flip Y
    if ($this->params['flip'] == 'Y') {
      $y = 1;
      $x = 0;
      while ( $y < $height ) {
        for ( $i = 0; $i < $width; $i++ ) {
          imagesetpixel( $output, $i, $y, imagecolorat( $gdimage, ( $i ), ( $height - $y ) ) );
        }
        $y++;
      }
      $this->media->setImage($output);
    }



  }


  public function getParams()
  {
    return array(
      array(
        'label' => rex_i18n::msg('media_manager_effect_flip'),
        'name' => 'flip',
        'type'  => 'select',
        'options' => $this->options,
        'default' => 'X'
      ),
    );
  }
}
