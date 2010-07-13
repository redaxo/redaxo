<?php

/**
 * Spiegel ein Bild
 */

class rex_effect_flip extends rex_effect_abstract
{
	var $options;

	function rex_effect_flip()
	{
		$this->options = array(
      'X','Y'
      );
	}

	function execute()
	{

		$gdimage =& $this->image->getImage();
		$w = $this->image->getWidth();
		$h = $this->image->getHeight();

		$im = $gdimage;

		$width = imagesx ( $im );
		$height = imagesy ( $im );
		$output_image_resource = imagecreatetruecolor ( $width, $height );

		// --------------- Flip X
		if($this->params['flip'] == "X")
		{
			$y = 0;
			$x = 1;

			while ( $x <= $width )
			{
				for ( $i = 0; $i < $height; $i++ )
				{
					imagesetpixel ( $output_image_resource, $x, $i, imagecolorat ( $im, ( $width - $x ), ( $i ) ) );
				}
				$x++;
			}
      $gdimage = $output_image_resource;
		}
		
		// --------------- Flip Y
		if($this->params['flip'] == "Y")
		{
			$y = 1;
			$x = 0;
			while ( $y < $height )
			{
				for ( $i = 0; $i < $width; $i++ )
				{
					imagesetpixel ( $output_image_resource, $i, $y, imagecolorat ( $im, ( $i ), ( $height - $y ) ) );
				}
				$y++;
			}
      $gdimage = $output_image_resource;
		}

	}


	function getParams()
	{
		global $REX,$I18N;

		return array(
		array(
        'label' => $I18N->msg('imanager_effect_flip'),
        'name' => 'flip',
        'type'  => 'select',
        'options' => $this->options,
        'default' => 'X'
        ),
        );
	}
}