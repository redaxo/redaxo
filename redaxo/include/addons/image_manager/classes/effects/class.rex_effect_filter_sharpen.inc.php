<?php

////////////////////////////////////////////////////////////////////////////////////////////////
////
////                  Unsharp Mask for PHP - version 2.1.1
////
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.
////             thoensi_at_netcom_dot_no.
////               Please leave this notice.
////
///////////////////////////////////////////////////////////////////////////////////////////////

// $img is an image that is already created within php using
// imgcreatetruecolor. No url! $img must be a truecolor image.

class rex_effect_filter_sharpen extends rex_effect_abstract{

	function execute()
	{

		// Attempt to calibrate the parameters to Photoshop:
		if ($this->params['amount'] > 500)
		  $this->params['amount'] = 500;
		$this->params['amount'] = $this->params['amount'] * 0.016;
		
		if ($this->params['radius'] > 50)
		  $this->params['radius'] = 50;
	  $this->params['radius'] = $this->params['radius'] * 2;
	  
		if ($this->params['threshold'] > 255)
		  $this->params['threshold'] = 255;
			
		$this->params['radius'] = abs(round($this->params['radius']));     // Only integers make sense.
		
		if ($this->params['radius'] == 0) {
			return; 
	  }
      $gdimage =& $this->image->getImage();
      $w = $this->image->getWidth();
      $h = $this->image->getHeight();
      
			$imgCanvas = imagecreatetruecolor($w, $h);
			$imgBlur = imagecreatetruecolor($w, $h);

			// Gaussian blur matrix:
			//
			//    1    2    1
			//    2    4    2
			//    1    2    1
			//
			//////////////////////////////////////////////////


			if (function_exists('imageconvolution')) { // PHP >= 5.1
				$matrix = array(
				array( 1, 2, 1 ),
				array( 2, 4, 2 ),
				array( 1, 2, 1 )
				);
				imagecopy ($imgBlur, $gdimage, 0, 0, 0, 0, $w, $h);
				imageconvolution($imgBlur, $matrix, 16, 0);
			}
			else {

				// Move copies of the image around one pixel at the time and merge them with weight
				// according to the matrix. The same matrix is simply repeated for higher radii.
				for ($i = 0; $i < $this->params['radius']; $i++)    {
					imagecopy ($imgBlur, $gdimage, 0, 0, 1, 0, $w - 1, $h); // left
					imagecopymerge ($imgBlur, $gdimage, 1, 0, 0, 0, $w, $h, 50); // right
					imagecopymerge ($imgBlur, $gdimage, 0, 0, 0, 0, $w, $h, 50); // center
					imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

					imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
					imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
				}
			}

			if($this->params['threshold']>0){
				// Calculate the difference between the blurred pixels and the original
				// and set the pixels
				for ($x = 0; $x < $w-1; $x++)    { // each row
					for ($y = 0; $y < $h; $y++)    { // each pixel
							
						$rgbOrig = ImageColorAt($gdimage, $x, $y);
						$rOrig = (($rgbOrig >> 16) & 0xFF);
						$gOrig = (($rgbOrig >> 8) & 0xFF);
						$bOrig = ($rgbOrig & 0xFF);
							
						$rgbBlur = ImageColorAt($imgBlur, $x, $y);
							
						$rBlur = (($rgbBlur >> 16) & 0xFF);
						$gBlur = (($rgbBlur >> 8) & 0xFF);
						$bBlur = ($rgbBlur & 0xFF);
							
						// When the masked pixels differ less from the original
						// than the threshold specifies, they are set to their original value.
						$rNew = (abs($rOrig - $rBlur) >= $this->params['threshold'])
						? max(0, min(255, ($this->params['amount'] * ($rOrig - $rBlur)) + $rOrig))
						: $rOrig;
						$gNew = (abs($gOrig - $gBlur) >= $this->params['threshold'])
						? max(0, min(255, ($this->params['amount'] * ($gOrig - $gBlur)) + $gOrig))
						: $gOrig;
						$bNew = (abs($bOrig - $bBlur) >= $this->params['threshold'])
						? max(0, min(255, ($this->params['amount'] * ($bOrig - $bBlur)) + $bOrig))
						: $bOrig;
							
							
							
						if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
							$pixCol = ImageColorAllocate($gdimage, $rNew, $gNew, $bNew);
							ImageSetPixel($gdimage, $x, $y, $pixCol);
						}
					}
				}
			}
			else{
				for ($x = 0; $x < $w; $x++)    { // each row
					for ($y = 0; $y < $h; $y++)    { // each pixel
						$rgbOrig = ImageColorAt($gdimage, $x, $y);
						$rOrig = (($rgbOrig >> 16) & 0xFF);
						$gOrig = (($rgbOrig >> 8) & 0xFF);
						$bOrig = ($rgbOrig & 0xFF);
							
						$rgbBlur = ImageColorAt($imgBlur, $x, $y);
							
						$rBlur = (($rgbBlur >> 16) & 0xFF);
						$gBlur = (($rgbBlur >> 8) & 0xFF);
						$bBlur = ($rgbBlur & 0xFF);
							
						$rNew = ($this->params['amount'] * ($rOrig - $rBlur)) + $rOrig;
						if($rNew>255){$rNew=255;}
						elseif($rNew<0){$rNew=0;}
						$gNew = ($this->params['amount'] * ($gOrig - $gBlur)) + $gOrig;
						if($gNew>255){$gNew=255;}
						elseif($gNew<0){$gNew=0;}
						$bNew = ($this->params['amount'] * ($bOrig - $bBlur)) + $bOrig;
						if($bNew>255){$bNew=255;}
						elseif($bNew<0){$bNew=0;}
						$rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;
						ImageSetPixel($gdimage, $x, $y, $rgbNew);
					}
				}
			}
			imagedestroy($imgCanvas);
			imagedestroy($imgBlur);
	}

	function getParams()
	{
		global $REX,$I18N;

		return array(
		array(
        'label' => $I18N->msg('imanager_effect_sharpen_amount'),
        'name' => 'amount',
        'type'  => 'int',
        'default' => '80'
        ),
        array(
        'label' => $I18N->msg('imanager_effect_sharpen_radius'),
        'name' => 'radius',
        'type'  => 'int',
        'default' => '0.5'
        ),
        array(
        'label' => $I18N->msg('imanager_effect_sharpen_threshold'),
        'name' => 'threshold',
        'type'  => 'int',
        'default' => '3'
        )
        );

	}

}
